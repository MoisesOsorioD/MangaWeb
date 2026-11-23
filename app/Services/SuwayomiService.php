<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SuwayomiService
{
    public function query(string $graphql, array $variables = []): array
    {
        $url = rtrim((string) config('services.suwayomi.url'), '/');
        $request = Http::timeout(35)->connectTimeout(5)->acceptJson()->asJson()
            ->withOptions(['allow_redirects' => false]);
        $username = (string) config('services.suwayomi.username');
        if ($username !== '') {
            $request = $request->withBasicAuth($username, (string) config('services.suwayomi.password'));
        }
        // GraphQL requires variables to be a JSON object, not an empty array.
        // When the operation has no variables, omit the field altogether.
        $payload = ['query' => $graphql];
        if ($variables !== []) {
            $payload['variables'] = $variables;
        }
        $response = $request->post($url.'/api/graphql', $payload);
        if (!$response->successful()) {
            // Preserve any concise error message provided by Suwayomi.
            $detail = $response->json('errors.0.message') ?? $response->json('message');
            $detail = is_string($detail) ? trim(substr($detail, 0, 350)) : '';
            throw new RuntimeException(
                'Suwayomi respondio HTTP '.$response->status().
                ($detail !== '' ? ': '.$detail : '. Comprueba el registro del servidor Java.')
            );
        }
        $errors = $response->json('errors');
        if (is_array($errors) && $errors !== []) {
            $message = $errors[0]['message'] ?? 'Error GraphQL';
            throw new RuntimeException('Suwayomi GraphQL: '.substr((string) $message, 0, 450));
        }
        return $response->json('data') ?? [];
    }

    public function sources(): array
    {
        $data = $this->query('query { sources { nodes { id name lang } } }');
        return $data['sources']['nodes'] ?? [];
    }

    public function browse(string $sourceId, int $page = 1, string $type = 'POPULAR', string $search = ''): array
    {
        if (!in_array($type, ['POPULAR','LATEST','SEARCH'], true)) $type = 'POPULAR';
        $input = ['source' => $sourceId, 'page' => $page, 'type' => $type];
        if ($type === 'SEARCH') $input['query'] = $search;
        $data = $this->query('mutation($input: FetchSourceMangaInput!) {
            fetchSourceManga(input: $input) { hasNextPage mangas { id title thumbnailUrl } }
        }', ['input' => $input]);
        return $data['fetchSourceManga'] ?? ['hasNextPage' => false, 'mangas' => []];
    }

    public function manga(string $id): array
    {
        $data = $this->query('mutation($input: FetchMangaInput!) {
            fetchManga(input: $input) { manga { id title description author artist genre status } }
        }', ['input' => ['id' => (int) $id]]);
        return $data['fetchManga']['manga'] ?? [];
    }

    public function chapters(string $mangaId): array
    {
        $data = $this->query('mutation($input: FetchChaptersInput!) {
            fetchChapters(input: $input) { chapters { id name chapterNumber scanlator } }
        }', ['input' => ['mangaId' => (int) $mangaId]]);
        return $data['fetchChapters']['chapters'] ?? [];
    }

    public function chapterPages(string $chapterId): array
    {
        $data = $this->query('mutation($input: FetchChapterPagesInput!) {
            fetchChapterPages(input: $input) { pages }
        }', ['input' => ['chapterId' => (int) $chapterId]]);
        return $data['fetchChapterPages']['pages'] ?? [];
    }

    public function extensions(): array
    {
        $data = $this->query('mutation { fetchExtensions(input: {}) {
            extensions { name pkgName lang isInstalled versionName hasUpdate }
        } }');
        return $data['fetchExtensions']['extensions'] ?? [];
    }
    /** La modificación de extensiones está desactivada salvo autorización expresa en .env. */
    public function updateExtension(string $package,string $action):array
    {
        if (!in_array($action,['install','update','uninstall'],true)) throw new RuntimeException('Acción incorrecta.');
        $data=$this->query('mutation($input: UpdateExtensionInput!) { updateExtension(input:$input) { extension { pkgName name isInstalled hasUpdate versionName } } }',[
            'input'=>['id'=>$package,'patch'=>[$action=>true]],
        ]);
        return $data['updateExtension']['extension']??[];
    }

    public function image(string $path):array {
        if (!preg_match('~^/api/v1/manga/\d+/(?:thumbnail|chapter/\d+/page/\d+)$~D',$path)) throw new RuntimeException('Ruta de imagen inválida.');
        $url=rtrim((string)config('services.suwayomi.url'),'/');
        $request=Http::timeout(40)->connectTimeout(5)->withOptions(['allow_redirects'=>false]);
        $username=(string)config('services.suwayomi.username');
        if($username!=='')$request=$request->withBasicAuth($username,(string)config('services.suwayomi.password'));
        $res=$request->get($url.$path);
        if(!$res->successful())throw new RuntimeException('No se pudo recuperar una imagen (HTTP '.$res->status().').');
        $type=strtolower(trim(explode(';',(string)$res->header('Content-Type'))[0]));
        if($type==='image/jpg')$type='image/jpeg';
        if(!in_array($type,['image/jpeg','image/png','image/webp','image/gif','image/avif'],true))throw new RuntimeException('Suwayomi no devolvió una imagen.');
        $body=$res->body();
        if(strlen($body)>config('mangaweb.download_max_image_bytes'))throw new RuntimeException('Imagen demasiado grande para guardar.');
        return ['body'=>$body,'type'=>$type];
    }

}

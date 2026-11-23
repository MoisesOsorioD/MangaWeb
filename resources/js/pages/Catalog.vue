<script setup lang="ts">
import {ref} from 'vue';
import {Head,Link,router} from '@inertiajs/vue3';
import {ArrowLeft,BookOpen,Search,ChevronLeft,ChevronRight,Layers3} from 'lucide-vue-next';
import MangaCard from '../components/MangaCard.vue';
interface Manga{id:string|number;title:string;thumbnailUrl?:string|null}
const props=defineProps<{sourceId:string;mangas:Manga[];hasNextPage:boolean;page:number;type:string;q:string;error:string|null}>();
const searchTerm=ref(props.q);
function path(page:number,type:string,q=searchTerm.value){const params=new URLSearchParams({page:String(page),type});if(type==='SEARCH'&&q)params.set('q',q);return `/explore/source/${props.sourceId}?${params.toString()}`;}
function submitSearch(){const query=searchTerm.value.trim();router.get(path(1,query?'SEARCH':'POPULAR',query));}
</script>
<template>
<Head title="Catálogo"/>
<div class="content"><Link href="/explore" class="breadcrumb"><ArrowLeft :size="17"/> Volver a las fuentes</Link>
 <div class="page-head"><div><div class="eyebrow">✦ Biblioteca de origen</div><h1>Catálogo de mangas</h1><p class="lead">Explorá los títulos disponibles y elegí tu próxima lectura.</p><p class="tiny">ID de fuente: {{sourceId}}</p></div><span class="pill"><Layers3 :size="15"/> Página {{page}}</span></div>
 <div v-if="error" class="notice notice-error">{{error}}</div>
 <div class="panel" style="margin-bottom:22px"><form @submit.prevent="submitSearch" style="display:flex;gap:10px;flex-wrap:wrap"><div class="search-box" style="flex:1"><Search/><input v-model="searchTerm" placeholder="Buscar manga en esta fuente..." aria-label="Buscar manga"/></div><button type="submit" class="button primary">Buscar</button></form><div class="tabs" style="margin-top:16px"><Link :href="path(1,'POPULAR')" class="tab" :class="{active:type==='POPULAR'}">Populares</Link><Link :href="path(1,'LATEST')" class="tab" :class="{active:type==='LATEST'}">Recientes</Link><Link v-if="q" :href="path(1,'SEARCH',q)" class="tab" :class="{active:type==='SEARCH'}">Resultados: {{q}}</Link></div></div>
 <div class="section-head"><h2>{{type==='SEARCH'?'Resultados de búsqueda':type==='LATEST'?'Últimas novedades':'Mangas populares'}}</h2><span class="muted" style="font-size:13px">{{mangas.length}} mangas en esta página</span></div>
 <div v-if="mangas.length" class="manga-grid"><MangaCard v-for="manga in mangas" :key="manga.id" :id="manga.id" :title="manga.title" subtitle="Ver capítulos"/></div>
 <div v-else class="empty"><div class="empty-icon"><BookOpen/></div><h3>No se encontraron títulos</h3><p>Probá buscar otra palabra o elegí otro catálogo.</p></div>
 <div class="pagination"><Link v-if="page>1" class="button secondary" :href="path(page-1,type,q)"><ChevronLeft/> Anterior</Link><span class="pill gray">Página {{page}}</span><Link v-if="hasNextPage" class="button secondary" :href="path(page+1,type,q)">Siguiente <ChevronRight/></Link></div>
 </div>
</template>

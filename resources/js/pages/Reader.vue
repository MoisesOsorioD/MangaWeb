<script setup lang="ts">
import {ref,computed,watch,nextTick,onMounted,onBeforeUnmount} from 'vue';
import {Head,Link} from '@inertiajs/vue3';
import {ArrowLeft,BookOpen,Settings2,Images,ChevronLeft,ChevronRight,BookmarkCheck,RotateCcw,Rows3,RectangleHorizontal,Bookmark,Download} from 'lucide-vue-next';

type Width='compacto'|'normal'|'ancho';type Mode='vertical'|'individual';
const props=defineProps<{
 mangaId:string;chapterId:string;chapterName:string|null;mangaTitle:string|null;
 pages:string[];savedPage:number;previousChapter:string|null;nextChapter:string|null;error:string|null;startPage:number;downloaded:boolean;
}>();
const WIDTH_KEY='mangaweb.reader.width';const MODE_KEY='mangaweb.reader.mode';
const readingWidth=ref<Width>('normal');const mode=ref<Mode>('vertical');
const widthList:[Width,string][]=[['compacto','Compacto'],['normal','Normal'],['ancho','Ancho']];
const maxWidth=computed(()=>({compacto:'700px',normal:'1000px',ancho:'1400px'}[readingWidth.value]));
const currentPage=ref(1);const bookmarks=ref<number[]>([]);const bookmarkBusy=ref(false);const pagesContainer=ref<HTMLElement|null>(null);
const isMounted=ref(false);const resumeVisible=ref(false);const saving=ref(false);const savedSuccessfully=ref(false);
let frame:number|null=null;let timer:number|null=null;let allowSave=false;
const atEnd=computed(()=>currentPage.value>=props.pages.length);
const percent=computed(()=>props.pages.length?Math.round(currentPage.value/props.pages.length*100):0);
const currentBookmarked=computed(()=>bookmarks.value.includes(currentPage.value));
function clamp(n:number){return Math.min(Math.max(n,1),Math.max(1,props.pages.length));}
function jumpTo(page:number){currentPage.value=clamp(page);allowSave=true;if(mode.value==='vertical'){
 nextTick(()=>{const el=pagesContainer.value?.querySelector<HTMLElement>(`[data-reader-index="${currentPage.value}"]`);el?.scrollIntoView({behavior:'smooth',block:'start'});});
 }resumeVisible.value=false;}
function detectCurrentPage(){if(mode.value!=='vertical'||!isMounted.value)return;
 const els=pagesContainer.value?.querySelectorAll<HTMLElement>('[data-reader-index]');if(!els?.length)return;
 const line=window.innerHeight*.38;let n=1;
 for(let i=0;i<els.length;i++){const rect=els[i].getBoundingClientRect();if(rect.top<=line)n=i+1;else break;}
 if(resumeVisible.value)return;
 if(n!==currentPage.value){currentPage.value=clamp(n);allowSave=true;}
}
function scheduleUpdate(){if(frame!==null)return;frame=requestAnimationFrame(()=>{frame=null;detectCurrentPage();});}
function onScroll(){if(mode.value==='vertical'&&!resumeVisible.value){allowSave=true;scheduleUpdate();}}
function changeMode(value:Mode){mode.value=value;allowSave=true;resumeVisible.value=false;nextTick(()=>{if(value==='vertical')jumpTo(currentPage.value);});}
function keydown(e:KeyboardEvent){if(mode.value!=='individual')return;const target=e.target as HTMLElement|null;
 if(target&&['INPUT','TEXTAREA','SELECT'].includes(target.tagName))return;
 if(e.key==='ArrowRight'||e.key==='ArrowDown'){e.preventDefault();jumpTo(currentPage.value+1);}
 if(e.key==='ArrowLeft'||e.key==='ArrowUp'){e.preventDefault();jumpTo(currentPage.value-1);}
}
async function saveProgress(keepalive=false){if(!allowSave||!props.pages.length)return;
 const csrf=document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content||'';
 saving.value=true;
 try{const response=await fetch('/reading-progress',{
   method:'POST',credentials:'same-origin',keepalive,
   headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
   body:JSON.stringify({manga_id:props.mangaId,chapter_id:props.chapterId,
    page_number:clamp(currentPage.value),total_pages:props.pages.length,
    manga_title:props.mangaTitle||`Manga #${props.mangaId}`,
    chapter_name:props.chapterName||`Capítulo ${props.chapterId}`})
  });savedSuccessfully.value=response.ok;
 }catch{savedSuccessfully.value=false;}finally{saving.value=false;}
}
function scheduleSave(){if(!allowSave||!isMounted.value||!props.pages.length)return;
 if(timer!==null)window.clearTimeout(timer);
 timer=window.setTimeout(()=>{timer=null;void saveProgress();},900);
}
watch(currentPage,scheduleSave);
watch(readingWidth,w=>{try{localStorage.setItem(WIDTH_KEY,w);}catch{}nextTick(scheduleUpdate);});
watch(mode,m=>{try{localStorage.setItem(MODE_KEY,m);}catch{}nextTick(scheduleUpdate);});
watch(()=>props.chapterId,async()=>{
 allowSave=false;currentPage.value=clamp(props.savedPage||1);resumeVisible.value=props.savedPage>1&&mode.value==='vertical';
 await nextTick();scheduleUpdate();
});
async function fetchBookmarks(){try{const response=await fetch(`/bookmarks/${props.mangaId}/${props.chapterId}`,{headers:{Accept:'application/json'},credentials:'same-origin'});if(response.ok){const data=await response.json();bookmarks.value=Array.isArray(data.pages)?data.pages.map(Number):[];}}catch{}}
async function toggleBookmark(){if(bookmarkBusy.value)return;bookmarkBusy.value=true;const csrf=document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content||'';
 try{const response=await fetch('/bookmarks/toggle',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({manga_id:props.mangaId,chapter_id:props.chapterId,page_number:currentPage.value,label:props.chapterName||null})});if(response.ok){const data=await response.json();if(data.bookmarked)bookmarks.value=[...bookmarks.value,currentPage.value];else bookmarks.value=bookmarks.value.filter(p=>p!==currentPage.value);}}catch{}finally{bookmarkBusy.value=false;}}
onMounted(async()=>{
 try{const w=localStorage.getItem(WIDTH_KEY);if(w==='compacto'||w==='normal'||w==='ancho')readingWidth.value=w;
 const m=localStorage.getItem(MODE_KEY);if(m==='vertical'||m==='individual')mode.value=m;}catch{}
 currentPage.value=clamp(props.startPage||props.savedPage||1);
 resumeVisible.value=(props.startPage||props.savedPage)>1&&mode.value==='vertical';
 await fetchBookmarks();
 await nextTick();isMounted.value=true;
 window.addEventListener('scroll',onScroll,{passive:true});
 document.addEventListener('scroll',onScroll,true);
 window.addEventListener('resize',scheduleUpdate);
 window.addEventListener('keydown',keydown);
 if(!resumeVisible.value)scheduleUpdate();
});
onBeforeUnmount(()=>{window.removeEventListener('scroll',onScroll);document.removeEventListener('scroll',onScroll,true);
 window.removeEventListener('resize',scheduleUpdate);window.removeEventListener('keydown',keydown);
 if(frame!==null)cancelAnimationFrame(frame);if(timer!==null){clearTimeout(timer);void saveProgress(true);}
});
</script>
<template>
<Head :title="chapterName||'Lector de capítulos'"/>
<div class="reader-full">
 <div class="reader-top"><Link :href="`/manga/${mangaId}`" class="button quiet"><ArrowLeft/> Volver al manga</Link>
 <div style="display:flex;align-items:center;gap:9px;color:#c4b4fc;font-weight:800"><BookOpen :size="19"/> MANGAWEB READER</div>
 <div class="pill">{{pages.length}} páginas</div></div>
 <div class="reader-top" style="background:#10101b"><div><div class="eyebrow" style="margin-bottom:5px">✦ Leyendo ahora</div><h2 style="font-size:18px;margin:0">{{chapterName||`Capítulo ${chapterId}`}}</h2></div>
 <div class="reader-tools"><Settings2 :size="18" class="muted"/><button v-for="option in widthList" :key="option[0]" type="button" class="reader-pill" :class="{selected:readingWidth===option[0]}" @click="readingWidth=option[0]">{{option[1]}}</button></div>
 <div class="reader-tools"><button class="reader-pill" :class="{selected:mode==='vertical'}" @click="changeMode('vertical')"><Rows3 :size="13" style="vertical-align:middle"/> Vertical</button><button class="reader-pill" :class="{selected:mode==='individual'}" @click="changeMode('individual')"><RectangleHorizontal :size="13" style="vertical-align:middle"/> Individual</button></div><div class="reader-tools"><span v-if="downloaded" class="pill green"><Download :size="13"/> Guardado en servidor</span><button class="reader-pill" :disabled="bookmarkBusy||!pages.length" @click="toggleBookmark"><BookmarkCheck v-if="currentBookmarked" :size="14" style="vertical-align:middle"/><Bookmark v-else :size="14" style="vertical-align:middle"/> {{currentBookmarked?'Quitar marcador':'Marcar página'}}</button><Link href="/bookmarks" class="reader-pill">Mis marcadores</Link></div></div>
 <div v-if="error" class="notice notice-error" style="margin:24px auto;max-width:900px">{{error}}</div>
 <div v-if="pages.length" class="reader-body">
  <div v-if="resumeVisible" class="notice" style="max-width:650px;margin:18px auto;text-align:center"><BookmarkCheck :size="18" style="vertical-align:middle"/> Tenés una página guardada para continuar: {{startPage||savedPage}}.
    <button class="button primary" style="margin-left:10px" @click="jumpTo(startPage||savedPage)"><RotateCcw/> Continuar lectura</button>
    <button class="button quiet" style="margin-left:6px" @click="resumeVisible=false;currentPage=1;allowSave=true;">Empezar desde el inicio</button></div>
  <p v-if="mode==='vertical'" class="muted" style="font-size:12px;text-align:center;padding:19px 8px 8px">Deslizá hacia abajo para seguir leyendo · {{percent}}% del capítulo</p>
  <div v-if="mode==='vertical'" ref="pagesContainer" class="reader-images" :style="{maxWidth}">
   <div v-for="(page,index) in pages" :key="`${chapterId}-${index}`" :data-reader-index="index+1" style="width:100%;background:#191923;min-height:40px">
    <img :src="page" :alt="`Página ${index+1}`" class="reader-image" :loading="index<3?'eager':'lazy'" decoding="async" @load="scheduleUpdate"/>
   </div>
  </div>
  <div v-else class="reader-images" :style="{maxWidth,padding:'20px 12px'}"><div class="reader-page-label" style="text-align:center;margin-bottom:12px">Página {{currentPage}} / {{pages.length}}</div>
   <img v-if="pages[currentPage-1]" :key="`${chapterId}-${currentPage}`" :src="pages[currentPage-1]" :alt="`Página ${currentPage}`" class="reader-image"/>
   <div class="page-controls" style="justify-content:space-between;margin-top:17px"><button class="button secondary" :disabled="currentPage<=1" @click="jumpTo(currentPage-1)"><ChevronLeft/> Anterior</button><span class="muted">{{percent}}% leído</span><button class="button secondary" :disabled="atEnd" @click="jumpTo(currentPage+1)">Siguiente <ChevronRight/></button></div>
   <p class="light-note" style="text-align:center;margin-top:15px">También podés usar las flechas ← y → del teclado.</p>
  </div>
  <div class="panel" style="max-width:880px;margin:30px auto;text-align:center"><h2>¡Final del capítulo!</h2><p class="muted">{{pages.length}} páginas disponibles. Tu progreso se guarda automáticamente al avanzar.</p>
   <div class="page-controls" style="justify-content:center"><Link v-if="previousChapter" class="button secondary" :href="`/manga/${mangaId}/read/${previousChapter}`"><ChevronLeft/> Capítulo anterior</Link>
   <Link v-if="nextChapter" class="button primary" :href="`/manga/${mangaId}/read/${nextChapter}`">Capítulo siguiente <ChevronRight/></Link><Link class="button secondary" :href="`/manga/${mangaId}`">Lista de capítulos</Link></div></div>
 </div>
 <div v-else class="empty" style="max-width:650px;margin:35px auto"><div class="empty-icon"><Images/></div><h3>No hay páginas para mostrar</h3><p>Revisá la fuente seleccionada y la conexión con Suwayomi.</p><Link class="button secondary" :href="`/manga/${mangaId}`">Volver al manga</Link></div>
 <div v-if="pages.length" class="reader-float"><BookOpen :size="15" style="vertical-align:middle;margin-right:4px"/> Página {{currentPage}} de {{pages.length}} <span style="color:#9f91b8;font-weight:500">· {{saving?'Guardando…':savedSuccessfully?'Guardado':'Lectura'}}</span></div>
</div>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import {Head,Link,router} from '@inertiajs/vue3';
import {ArrowLeft,BookOpen,UserRound,Palette,Tags,BookMarked,BookmarkPlus,BookmarkMinus,ChevronRight,PlayCircle,Layers,Download,CheckCircle} from 'lucide-vue-next';
import {readPercent} from '../utils';
interface Manga{id:string|number;title:string;description:string|null;author:string|null;artist:string|null;genre:string[]|null;status:string|null}
interface Chapter{id:string|number;name:string;chapterNumber:number;scanlator:string|null}
interface Progress{chapter_id:string;page_number:number;total_pages:number}
interface DownloadInfo{status:string;saved_pages:number;total_pages:number}
const props=defineProps<{manga:Partial<Manga>;chapters:Chapter[];favorite:boolean;progress:Record<string,Progress>;downloads:Record<string,DownloadInfo>;error:string|null}>();
const id=computed(()=>String(props.manga.id??''));
const state=computed(()=>({ONGOING:'En publicación',COMPLETED:'Finalizado',CANCELLED:'Cancelado',HIATUS:'En pausa',ON_HIATUS:'En pausa'} as Record<string,string>)[props.manga.status??'']??'Estado desconocido');
const resume=computed(()=>props.chapters.find(c=>props.progress[String(c.id)]&&props.progress[String(c.id)].page_number<props.progress[String(c.id)].total_pages));
function downloadChapter(c:Chapter){router.post('/downloads',{manga_id:id.value,chapter_id:String(c.id),manga_title:props.manga.title??'',chapter_name:c.name},{preserveScroll:true});}
function toggle(){if(!id.value)return;router.post('/library/toggle',{manga_id:id.value,source_id:null,title:props.manga.title??`Manga ${id.value}`},{preserveScroll:true});}
</script>
<template>
<Head :title="manga.title||'Detalles del manga'"/>
<div class="content"><Link href="/explore" class="breadcrumb"><ArrowLeft :size="16"/> Volver a Explorar</Link>
<div v-if="error" class="notice notice-error">{{error}}</div>
<div v-if="manga.id" class="detail-layout"><div><img class="detail-cover" :src="`/manga/${id}/thumbnail`" :alt="`Portada de ${manga.title}`"/><div style="display:grid;gap:9px;margin-top:16px"><button class="button" :class="favorite?'secondary':'primary'" @click="toggle"><BookmarkMinus v-if="favorite"/><BookmarkPlus v-else/> {{favorite?'Quitar de biblioteca':'Añadir a biblioteca'}}</button><Link v-if="resume" class="button secondary" :href="`/manga/${id}/read/${resume.id}`"><PlayCircle/> Continuar leyendo</Link><Link v-else-if="chapters.length" class="button secondary" :href="`/manga/${id}/read/${chapters[0].id}`"><BookOpen/> Leer primer capítulo</Link></div></div>
<div><div class="eyebrow">✦ MangaWeb · Detalles</div><h1 class="detail-title">{{manga.title}}</h1><div class="pill" style="margin-top:13px">{{state}}</div>
<div class="meta-cards"><div class="meta-card"><small><UserRound :size="14" style="vertical-align:middle"/> Autor</small><strong>{{manga.author||'Desconocido'}}</strong></div><div class="meta-card"><small><Palette :size="14" style="vertical-align:middle"/> Artista</small><strong>{{manga.artist||'Desconocido'}}</strong></div></div>
<h3 style="display:flex;align-items:center;gap:8px"><Tags :size="18" style="color:var(--accent)"/> Géneros</h3><div class="chips"><span v-for="g in manga.genre||[]" :key="g" class="chip">{{g}}</span><span v-if="!manga.genre?.length" class="muted">No disponibles</span></div>
<div class="panel" style="margin-top:26px"><h3><BookMarked :size="19" style="color:var(--accent);vertical-align:middle"/> Sinopsis</h3><p class="synopsis">{{manga.description||'Este manga todavía no tiene una descripción.'}}</p></div>
</div></div>
<div v-else-if="!error" class="empty">No se encontró el manga solicitado.</div>
<section style="margin-top:36px"><div class="section-head"><h2 style="display:flex;align-items:center;gap:9px"><Layers :size="21" style="color:var(--accent)"/> Capítulos disponibles</h2><span class="pill">{{chapters.length}} capítulo(s)</span></div>
<div v-if="chapters.length" class="chapter-list"><div v-for="chapter in chapters" :key="chapter.id" class="chapter" style="padding:8px 12px;gap:7px;flex-wrap:wrap">
 <Link :href="`/manga/${id}/read/${chapter.id}`" style="display:flex;align-items:center;gap:12px;flex:1;min-width:220px;padding:6px 2px">
 <div class="chapter-icon"><BookOpen :size="20"/></div><div class="chapter-main"><strong>{{chapter.name||`Capítulo ${chapter.chapterNumber}`}}</strong><small>Capítulo {{chapter.chapterNumber}} <span v-if="chapter.scanlator">· {{chapter.scanlator}}</span></small><div v-if="progress[String(chapter.id)]" class="progress-track" style="margin-top:9px;max-width:430px"><div class="progress-fill" :style="{width:readPercent(progress[String(chapter.id)].page_number,progress[String(chapter.id)].total_pages)+'%'}"/></div></div><span v-if="progress[String(chapter.id)]" class="pill gray">{{progress[String(chapter.id)].page_number}} / {{progress[String(chapter.id)].total_pages}}</span><span v-else class="pill">Leer</span><ChevronRight :size="17" class="muted"/>
 </Link>
 <span v-if="downloads[String(chapter.id)]?.status==='ready'" class="pill green"><CheckCircle :size="13"/> Descargado</span>
 <span v-else-if="downloads[String(chapter.id)]?.status==='queued'||downloads[String(chapter.id)]?.status==='running'" class="pill gray">En descarga</span>
 <button v-else class="button secondary" @click="downloadChapter(chapter)"><Download/> Descargar</button></div></div>
<div v-else class="empty"><div class="empty-icon"><BookOpen/></div><h3>No hay capítulos disponibles</h3><p>La fuente no devolvió capítulos de este título.</p></div>
</section></div>
</template>

<script setup lang="ts">
import { Head,Link } from '@inertiajs/vue3';
import { Compass,LibraryBig,BookOpen,Clock3,ArrowRight,BookMarked,History } from 'lucide-vue-next';
import MangaCard from '../components/MangaCard.vue';
import { readPercent } from '../utils';
interface Favorite{manga_id:string;title:string;source_id?:string|null}
interface Progress{manga_id:string;chapter_id:string;manga_title:string|null;chapter_name:string|null;page_number:number;total_pages:number}
defineProps<{libraryCount:number;recentLibrary:Favorite[];recentReading:Progress[]}>();
</script>
<template>
<Head title="Inicio"/>
<div class="content">
 <div class="page-head"><div><div class="eyebrow">✦ Tu biblioteca personal</div><h1>Bienvenido a MangaWeb</h1><p class="lead">Descubrí historias, organizá tus favoritos y retomá tus lecturas donde las dejaste.</p></div></div>
 <section class="hero"><div class="eyebrow">Historias infinitas, un solo lugar</div><h1>Tu próxima lectura está a un clic.</h1><p>Explorá catálogos de fuentes instaladas en Suwayomi y disfrutá de un lector hecho para vos.</p><div class="hero-actions"><Link href="/explore" class="button primary"><Compass/> Explorar mangas <ArrowRight/></Link><Link href="/library" class="button secondary"><LibraryBig/> Mi Biblioteca</Link></div></section>
 <section class="grid grid-3" style="margin-top:22px"><div class="stat"><div class="stat-head"><span>Mangas guardados</span><LibraryBig/></div><div class="stat-num">{{libraryCount}}</div><div class="stat-note">En tu biblioteca personal</div></div>
 <div class="stat"><div class="stat-head"><span>Lecturas recientes</span><Clock3/></div><div class="stat-num">{{recentReading.length}}</div><div class="stat-note">Últimos capítulos consultados</div></div>
 <div class="stat"><div class="stat-head"><span>Modo de lectura</span><BookOpen/></div><div class="stat-num" style="font-size:24px">Vertical</div><div class="stat-note">Y lectura página por página</div></div></section>
 <div class="section-head"><h2>Continuar leyendo</h2><Link class="inline-link" href="/history">Ver historial →</Link></div>
 <div v-if="recentReading.length" class="grid grid-2"><Link v-for="item in recentReading.slice(0,4)" :key="`${item.manga_id}-${item.chapter_id}`" :href="`/manga/${item.manga_id}/read/${item.chapter_id}`" class="history-item"><div class="badge-icon"><BookOpen :size="23"/></div><div style="flex:1;min-width:0"><p class="history-title">{{item.manga_title||`Manga #${item.manga_id}`}}</p><p class="history-sub">{{item.chapter_name||`Capítulo ${item.chapter_id}`}} · Página {{item.page_number}} de {{item.total_pages}}</p><div class="progress-track"><div class="progress-fill" :style="{width:readPercent(item.page_number,item.total_pages)+'%'}"/></div></div><ArrowRight :size="18"/></Link></div>
 <div v-else class="empty"><div class="empty-icon"><History/></div><h3>Aquí aparecerán tus lecturas</h3><p>Cuando comencés un capítulo, podrás retomarlo desde esta sección.</p></div>
 <div class="section-head"><h2>Mi Biblioteca</h2><Link class="inline-link" href="/library">Ver todo →</Link></div>
 <div v-if="recentLibrary.length" class="manga-grid"><MangaCard v-for="item in recentLibrary" :key="item.manga_id" :id="item.manga_id" :title="item.title" subtitle="En mi biblioteca"/></div>
 <div v-else class="empty"><div class="empty-icon"><BookMarked/></div><h3>Tu biblioteca te está esperando</h3><p>Agregá tus primeros mangas desde la sección Explorar.</p><Link href="/explore" class="button primary" style="margin-top:13px">Descubrir mangas</Link></div>
 <div class="footer-note">MangaWeb utiliza Suwayomi-Server para consultar contenido. Los archivos originales no se incluyen en esta aplicación.</div>
</div>
</template>

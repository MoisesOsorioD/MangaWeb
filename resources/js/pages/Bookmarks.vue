<script setup lang="ts">
import {Head,Link,router} from '@inertiajs/vue3';import {Bookmark,BookOpen,Trash2} from 'lucide-vue-next';
interface Item{manga_id:string;chapter_id:string;page_number:number;label:string|null;id:number}
defineProps<{items:Item[]}>();
function remove(x:Item){router.post('/bookmarks/toggle',{manga_id:x.manga_id,chapter_id:x.chapter_id,page_number:x.page_number},{preserveScroll:true});}
</script>
<template><Head title="Marcadores"/><div class="content"><div class="page-head"><div><div class="eyebrow">✦ Tus páginas favoritas</div><h1>Marcadores</h1><p class="lead">Volvé directamente a una página marcada de cualquier capítulo.</p></div><span class="pill"><Bookmark :size="15"/> {{items.length}} marcadores</span></div>
<div v-if="items.length" class="table-rows"><div v-for="b in items" :key="b.id" class="history-item"><div class="badge-icon"><Bookmark/></div><div style="flex:1"><strong>{{b.label||`Manga ${b.manga_id}`}}</strong><div class="history-sub">Capítulo {{b.chapter_id}} · Página {{b.page_number}}</div></div><Link :href="`/manga/${b.manga_id}/read/${b.chapter_id}?page=${b.page_number}`" class="button primary"><BookOpen/> Ir a la página</Link><button class="button danger" title="Eliminar marcador" @click="remove(b)"><Trash2/></button></div></div>
<div v-else class="empty"><div class="empty-icon"><Bookmark/></div><h3>Sin marcadores</h3><p>Mientras leés un capítulo, pulsá el botón para marcar la página actual.</p><Link href="/explore" class="button secondary">Explorar</Link></div></div></template>

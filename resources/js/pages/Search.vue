<script setup lang="ts">
import {ref} from 'vue';import {Head,router,Link} from '@inertiajs/vue3';import {Search,BookOpen} from 'lucide-vue-next';import MangaCard from '../components/MangaCard.vue';
interface Source{id:string;name:string;lang:string};interface Manga{id:string|number;title:string;sourceName:string}
const props=defineProps<{q:string;sourceId:string;sources:Source[];items:Manga[];error:string|null}>();
const term=ref(props.q);const source=ref(props.sourceId);
function go(){const q=term.value.trim();router.get('/search',q?{q,source_id:source.value||undefined}:{},{preserveState:true});}
</script>
<template><Head title="Buscar"/><div class="content"><div class="page-head"><div><div class="eyebrow">✦ Búsqueda</div><h1>Buscar historias</h1><p class="lead">Encontrá mangas en una fuente concreta o en las primeras cinco fuentes instaladas.</p></div></div>
<form class="panel" style="margin-bottom:24px" @submit.prevent="go"><div class="page-controls"><div class="search-box" style="flex:2"><Search/><input v-model="term" placeholder="Nombre del manga" aria-label="Buscar manga" maxlength="120"/></div><select v-model="source" class="input" style="flex:1;min-width:190px"><option value="">Primeras 5 fuentes</option><option v-for="s in sources" :key="s.id" :value="s.id">{{s.name}} ({{s.lang}})</option></select><button class="button primary" type="submit">Buscar</button></div></form>
<div v-if="error" class="notice notice-error">{{error}}</div><div class="section-head"><h2>{{q?`Resultados de: ${q}`:'Buscá tu próxima lectura'}}</h2><span class="pill">{{items.length}} encontrados</span></div>
<div v-if="items.length" class="manga-grid"><div v-for="m in items" :key="`${m.sourceName}-${m.id}`"><MangaCard :id="m.id" :title="m.title" :subtitle="m.sourceName"/></div></div><div v-else class="empty"><div class="empty-icon"><BookOpen/></div><h3>{{q?'Sin resultados en las fuentes consultadas':'Empezá a buscar'}}</h3><p>Instalá fuentes en Suwayomi para ampliar el catálogo.</p><Link href="/explore" class="button secondary">Explorar fuentes</Link></div>
</div></template>

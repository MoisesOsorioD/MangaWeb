<script setup lang="ts">
import {ref,computed} from 'vue';
import {Head,Link} from '@inertiajs/vue3';
import {Compass,Search,BookOpen,PlugZap,ArrowRight,Languages} from 'lucide-vue-next';
interface Source{id:string;name:string;lang:string}
const props=defineProps<{sources:Source[];error:string|null}>();
const term=ref('');const lang=ref('all');
const languages=computed(()=>[...new Set(props.sources.map(s=>s.lang))].sort());
const filtered=computed(()=>props.sources.filter(s=>(lang.value==='all'||s.lang===lang.value)&&`${s.name} ${s.lang}`.toLowerCase().includes(term.value.toLowerCase())));
</script>
<template>
<Head title="Explorar fuentes"/>
<div class="content"><div class="page-head"><div><div class="eyebrow">✦ Descubrimiento</div><h1>Explorá tus fuentes</h1><p class="lead">Elegí una de las fuentes instaladas en Suwayomi para descubrir mangas, manhwas y más.</p></div><span class="pill"><PlugZap :size="14"/> {{sources.length}} fuentes disponibles</span></div>
 <div v-if="error" class="notice notice-error">{{error}} <span style="display:block;margin-top:6px">Abrí Suwayomi en <code>http://127.0.0.1:4567</code> y verificá las fuentes.</span></div>
 <div class="panel" style="margin-bottom:24px;display:flex;flex-wrap:wrap;gap:13px"><div class="search-box" style="flex:1"><Search/><input v-model="term" aria-label="Buscar fuente" placeholder="Buscar fuente, por ejemplo MangaDex..."/></div><label style="display:flex;align-items:center;gap:9px"><Languages :size="18" class="muted"/><select v-model="lang" class="input" style="min-width:145px"><option value="all">Todos los idiomas</option><option v-for="code in languages" :key="code" :value="code">{{code}}</option></select></label></div>
 <div v-if="filtered.length" class="source-grid"><Link v-for="source in filtered" :key="source.id" :href="`/explore/source/${source.id}`" class="source"><div class="source-icon"><BookOpen :size="22"/></div><div style="flex:1;min-width:0"><div class="source-name" style="overflow-wrap:anywhere">{{source.name}}</div><div class="source-meta">{{source.lang}} · Fuente instalada</div></div><ArrowRight :size="17" class="muted"/></Link></div>
 <div v-else class="empty"><div class="empty-icon"><Compass/></div><h3>No hay fuentes para mostrar</h3><p>Instalá una extensión en Suwayomi o cambiá tus filtros de búsqueda.</p><Link href="/extensions" class="button secondary">Ver extensiones</Link></div>
 <p class="footer-note">El catálogo depende de las extensiones y los repositorios que tengas configurados en Suwayomi. MangaWeb no instala fuentes por sí mismo.</p>
</div>
</template>

<script setup lang="ts">
import {ref,computed} from 'vue';import {Head,Link,router} from '@inertiajs/vue3';import {Puzzle,Search,CheckCircle,Compass,RefreshCw} from 'lucide-vue-next';
interface Extension{name:string;pkgName:string;lang:string;isInstalled:boolean;versionName:string;hasUpdate?:boolean}
const props=defineProps<{extensions:Extension[];error:string|null;canManage:boolean}>();
const q=ref('');const only=ref(false);const processing=ref('');
const filtered=computed(()=>props.extensions.filter(e=>(!only.value||e.isInstalled)&&`${e.name} ${e.lang} ${e.pkgName}`.toLowerCase().includes(q.value.toLowerCase())).slice(0,500));
const installed=computed(()=>props.extensions.filter(e=>e.isInstalled).length);
function manage(e:Extension,action:'install'|'update'|'uninstall'){
 if(action==='uninstall'&&!confirm(`¿Desinstalar ${e.name}? La fuente podría dejar de funcionar.`))return;
 processing.value=e.pkgName;
 router.post('/extensions/manage',{pkg_name:e.pkgName,action},{preserveScroll:true,onFinish:()=>processing.value=''});
}
</script>
<template><Head title="Extensiones"/><div class="content"><div class="page-head"><div><div class="eyebrow">✦ Extensiones de Suwayomi</div><h1>Extensiones</h1><p class="lead">Consultá, filtrá y, si lo habilitaste explícitamente como administrador, instalá, actualizá o desinstalá extensiones desde sus repositorios configurados.</p></div><span class="pill"><Puzzle :size="14"/> {{installed}} instaladas</span></div>
<div v-if="error" class="notice notice-error">{{error}}</div>
<div v-if="!canManage" class="notice">La instalación está desactivada por seguridad. Para habilitarla en una instalación privada, configurá <code>ALLOW_EXTENSION_MANAGEMENT=true</code> en .env y recargá la configuración. Solo la primera cuenta puede administrarlas. También podés hacerlo desde Suwayomi.</div>
<div class="panel" style="display:flex;flex-wrap:wrap;gap:13px;align-items:center;margin-bottom:22px"><div class="search-box" style="flex:1"><Search/><input v-model="q" placeholder="Buscar extensión..."/></div><label style="display:flex;align-items:center;gap:8px;font-size:13px"><input v-model="only" type="checkbox"/> Solo instaladas</label><Link href="/extensions" class="button secondary"><RefreshCw/> Actualizar lista</Link></div>
<div v-if="filtered.length" class="source-grid"><div v-for="ext in filtered" :key="ext.pkgName" class="source" style="align-items:flex-start"><div class="source-icon"><Puzzle :size="21"/></div><div style="flex:1;min-width:0"><div class="source-name">{{ext.name}}</div><div class="source-meta">{{ext.lang}} · {{ext.versionName}}</div><div class="source-meta" style="overflow-wrap:anywhere">{{ext.pkgName}}</div><div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px"><span v-if="ext.isInstalled" class="pill green"><CheckCircle :size="13"/> Instalada</span><span v-else class="pill gray">Disponible</span><span v-if="ext.hasUpdate" class="pill">Actualización</span></div><div v-if="canManage" class="page-controls" style="margin-top:12px"><button v-if="!ext.isInstalled" class="button primary" :disabled="processing!==''" @click="manage(ext,'install')">Instalar</button><button v-if="ext.hasUpdate" class="button secondary" :disabled="processing!==''" @click="manage(ext,'update')">Actualizar</button><button v-if="ext.isInstalled" class="button danger" :disabled="processing!==''" @click="manage(ext,'uninstall')">Desinstalar</button></div></div></div></div>
<div v-else class="empty"><div class="empty-icon"><Puzzle/></div><h3>No hay extensiones para mostrar</h3><p>Comprobá el repositorio en Suwayomi y tu búsqueda.</p><Link href="/explore" class="button secondary"><Compass/> Explorar fuentes</Link></div>
<p class="footer-note">Una extensión es código de terceros que se ejecuta en Suwayomi. Instalá únicamente fuentes de confianza. La compatibilidad depende de su versión y de los permisos del servidor.</p>
</div></template>

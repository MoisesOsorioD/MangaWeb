<script setup lang="ts">
import {ref,computed} from 'vue';import {Head,router,Link} from '@inertiajs/vue3';import {Layers3,Trash2,Plus,BookOpen} from 'lucide-vue-next';import MangaCard from '../components/MangaCard.vue';
interface Shelf{id:number;name:string};interface Item{id:number;manga_id:string;title:string;shelf_id:number|null}
const props=defineProps<{shelves:Shelf[];items:Item[]}>();const name=ref('');const active=ref<number|null>(null);
const filtered=computed(()=>props.items.filter(x=>x.shelf_id===active.value));
function create(){if(!name.value.trim())return;router.post('/shelves',{name:name.value.trim()},{onSuccess:()=>name.value=''});}
function remove(s:Shelf){if(confirm(`¿Eliminar la colección «${s.name}»? Los mangas no se borrarán.`))router.delete(`/shelves/${s.id}`);}
</script>
<template><Head title="Colecciones"/><div class="content"><div class="page-head"><div><div class="eyebrow">✦ Biblioteca organizada</div><h1>Colecciones</h1><p class="lead">Organizá mangas en estanterías personalizadas, sin duplicarlos.</p></div></div>
<form @submit.prevent="create" class="panel page-controls" style="margin-bottom:20px"><input v-model="name" class="input" style="flex:1" placeholder="Nombre: Favoritos, Comedia, Pendientes..." maxlength="80"/><button class="button primary"><Plus/> Crear colección</button></form>
<div class="tabs" style="margin-bottom:18px"><button class="tab" :class="{active:active===null}" @click="active=null">Sin colección</button><div v-for="s in shelves" :key="s.id" class="page-controls"><button class="tab" :class="{active:active===s.id}" @click="active=s.id">{{s.name}}</button><button class="button danger" title="Eliminar colección" @click="remove(s)"><Trash2/></button></div></div>
<div class="notice">Para asignar una colección a un manga, abrí «Mi Biblioteca» y editá sus datos.</div>
<div v-if="filtered.length" class="manga-grid"><MangaCard v-for="m in filtered" :key="m.id" :id="m.manga_id" :title="m.title" subtitle="En tu colección"/></div><div v-else class="empty"><div class="empty-icon"><Layers3/></div><h3>Esta colección está vacía</h3><p>Agregá mangas desde Mi Biblioteca.</p><Link href="/library" class="button secondary"><BookOpen/> Mi biblioteca</Link></div></div></template>

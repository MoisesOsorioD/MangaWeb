<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, LayoutDashboard, Compass, LibraryBig, Clock3, Puzzle, Settings2, LogOut, Search, Download, BookmarkCheck, Layers3 } from 'lucide-vue-next';
const page = usePage();
const url = computed(() => page.url.split('?')[0]);
const user = computed(() => (page.props.auth as {user?:{name:string,email:string}}|undefined)?.user);
const flash = computed(() => (page.props.flash as {success?:string}|undefined)?.success);
const errors = computed(() => (page.props.errors as Record<string,string>|undefined)??{});
const nav = [
 {label:'Inicio',href:'/dashboard',icon:LayoutDashboard},
 {label:'Explorar',href:'/explore',icon:Compass},
 {label:'Buscar',href:'/search',icon:Search},
 {label:'Mi biblioteca',href:'/library',icon:LibraryBig},
 {label:'Historial',href:'/history',icon:Clock3},
 {label:'Colecciones',href:'/shelves',icon:Layers3},
 {label:'Marcadores',href:'/bookmarks',icon:BookmarkCheck},
 {label:'Descargas',href:'/downloads',icon:Download},
 {label:'Extensiones',href:'/extensions',icon:Puzzle},
 {label:'Configuración',href:'/settings',icon:Settings2},
];
const active=(href:string)=>url.value===href||(href==='/explore'&&url.value.startsWith('/explore/'));
</script>
<template>
<div class="app-shell">
 <aside class="sidebar">
  <Link href="/dashboard" class="logo"><span class="logo-symbol"><BookOpen :size="23"/></span><span>Manga<em>Web</em></span></Link>
  <div><div class="sidebar-label">Biblioteca digital</div>
   <nav class="nav-list"><Link v-for="item in nav" :key="item.href" :href="item.href" class="nav-link" :class="{active:active(item.href)}"><component :is="item.icon"/>{{item.label}}</Link></nav>
  </div>
  <div class="sidebar-bottom"><div class="side-note"><strong>✦ Tu espacio de lectura</strong>Explorá tus fuentes, agregá favoritos y retomá capítulos donde los dejaste.</div></div>
 </aside>
 <div class="main-area">
  <header class="topbar">
   <div class="topbar-left"><div><div class="topbar-title">MangaWeb</div><div class="topbar-sub">Tu biblioteca, a tu manera</div></div></div>
   <div class="topbar-right"><Link href="/explore" class="button quiet"><Search/> <span class="desktop-only">Buscar mangas</span></Link>
    <div class="user-pill"><div class="avatar">{{(user?.name||'?').charAt(0).toUpperCase()}}</div><span>{{user?.name}}</span></div>
    <Link href="/logout" method="post" as="button" class="button quiet" title="Cerrar sesión"><LogOut/></Link>
   </div>
  </header>
  <div v-if="flash" class="notice" style="margin:15px 36px 0">{{flash}}</div><div v-if="Object.keys(errors).length" class="notice notice-error" style="margin:15px 36px 0"><p v-for="(message,key) in errors" :key="key">{{message}}</p></div>
  <slot/>
 </div>
 <nav class="mobile-nav">
  <Link v-for="item in nav.filter(item=>['/dashboard','/explore','/library','/downloads','/history'].includes(item.href))" :key="item.href" :href="item.href" :class="{active:active(item.href)}"><component :is="item.icon"/>{{item.label}}</Link>
 </nav>
</div>
</template>

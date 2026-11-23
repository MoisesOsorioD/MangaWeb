<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen } from 'lucide-vue-next';
defineProps<{id:string|number;title:string;subtitle?:string}>();
function hideBrokenImage(event:Event):void{(event.target as HTMLImageElement).style.opacity='0';}
</script>
<template>
 <Link :href="`/manga/${id}`" class="manga-card">
  <div class="cover-box"><img :src="`/manga/${id}/thumbnail`" :alt="`Portada de ${title}`" loading="lazy" class="cover" @error="hideBrokenImage"/></div>
  <div class="manga-card-body"><div class="manga-card-title">{{title}}</div><div class="tiny" style="margin-top:9px">{{subtitle||'Ver detalles y capítulos'}}</div></div>
 </Link>
</template>

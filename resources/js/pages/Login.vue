<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { BookOpen, ArrowRight } from 'lucide-vue-next';
const form=useForm({email:'',password:'',remember:true});
const submit=()=>form.post('/login',{onFinish:()=>form.reset('password')});
</script>
<template>
<Head title="Iniciar sesión"/>
<div class="auth-wrap">
 <div class="auth-art"><div class="eyebrow">✦ Tu universo de historias</div><h1>Tu próxima historia comienza aquí.</h1><p>Explorá fuentes, descubrí mangas y continuá leyendo desde tu última página. Todo en un solo lugar.</p><div class="pill" style="align-self:flex-start;margin-top:25px">MANGAWEB · PERSONAL</div></div>
 <div class="auth-pane"><div class="auth-card"><div class="auth-brand"><span class="logo-symbol" style="margin:auto"><BookOpen/></span><h2>MangaWeb</h2></div><div class="eyebrow">Bienvenido de nuevo</div><h2>Iniciar sesión</h2><p class="muted">Ingresá a tu biblioteca personal.</p>
  <form @submit.prevent="submit" style="margin-top:30px"><div class="field"><label for="email">Correo electrónico</label><input id="email" v-model="form.email" class="input" type="email" autocomplete="email" required placeholder="tu@correo.com"/><div v-if="form.errors.email" class="error-text">{{form.errors.email}}</div></div>
   <div class="field"><label for="password">Contraseña</label><input id="password" v-model="form.password" class="input" type="password" autocomplete="current-password" required/><div v-if="form.errors.password" class="error-text">{{form.errors.password}}</div></div>
   <label style="display:flex;align-items:center;gap:8px;margin:12px 0 23px;font-size:13px;color:#aeb0c4"><input v-model="form.remember" type="checkbox"/> Recordar sesión</label>
   <button type="submit" class="button primary auth-button" :disabled="form.processing">Ingresar <ArrowRight :size="17"/></button>
  </form><p class="light-note" style="margin-top:24px">¿Primera vez? <Link href="/register" class="inline-link">Crear una cuenta</Link> (si el registro está habilitado).</p>
 </div></div>
</div>
</template>

<script setup lang="ts">
import { Head,Link,useForm } from '@inertiajs/vue3';
import { BookOpen,ArrowRight } from 'lucide-vue-next';
const form=useForm({name:'',email:'',password:'',password_confirmation:''});
const submit=()=>form.post('/register',{onFinish:()=>form.reset('password','password_confirmation')});
</script>
<template>
<Head title="Crear cuenta"/>
<div class="auth-wrap"><div class="auth-art"><div class="eyebrow">✦ Una biblioteca solo tuya</div><h1>Guardá cada historia. Seguí cada aventura.</h1><p>Creá tu cuenta para guardar tu biblioteca y tu progreso de lectura en MySQL.</p></div>
<div class="auth-pane"><div class="auth-card"><div class="auth-brand"><span class="logo-symbol" style="margin:auto"><BookOpen/></span><h2>MangaWeb</h2></div><div class="eyebrow">Empezá a explorar</div><h2>Crear cuenta</h2><p class="muted">Tus mangas y tu progreso quedarán asociados a esta cuenta.</p>
<form @submit.prevent="submit" style="margin-top:24px">
<div class="field"><label for="name">Nombre</label><input id="name" v-model="form.name" class="input" required maxlength="80" autocomplete="name"/><div v-if="form.errors.name" class="error-text">{{form.errors.name}}</div></div>
<div class="field"><label for="email">Correo electrónico</label><input id="email" v-model="form.email" class="input" type="email" required autocomplete="email"/><div v-if="form.errors.email" class="error-text">{{form.errors.email}}</div></div>
<div class="field"><label for="password">Contraseña (mínimo 10 caracteres)</label><input id="password" v-model="form.password" class="input" type="password" minlength="10" required autocomplete="new-password"/><div v-if="form.errors.password" class="error-text">{{form.errors.password}}</div></div>
<div class="field"><label for="confirm">Confirmar contraseña</label><input id="confirm" v-model="form.password_confirmation" class="input" type="password" required autocomplete="new-password"/></div>
<button type="submit" class="button primary auth-button" :disabled="form.processing">Crear mi biblioteca <ArrowRight :size="17"/></button>
</form><p class="light-note" style="margin-top:24px">¿Ya tenés cuenta? <Link href="/login" class="inline-link">Iniciá sesión</Link>.</p>
</div></div></div>
</template>

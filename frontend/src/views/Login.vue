<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-sm" style="width: 100%; max-width: 420px;">
      <div class="card-body p-4">
        <h1 class="card-title text-center mb-4 h4">Logowanie</h1>
        <form @submit.prevent="handleLogin" novalidate>
          <div class="mb-3">
            <label for="login" class="form-label">Login (imię + nazwisko)</label>
            <input
              id="login"
              v-model="form.login"
              type="text"
              class="form-control"
              :class="{ 'is-invalid': errors.login }"
              placeholder="np. PiotrKowalski"
              autocomplete="username"
              @blur="validateField('login')"
            />
            <div class="invalid-feedback">{{ errors.login }}</div>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Hasło (data urodzenia)</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="form-control"
              :class="{ 'is-invalid': errors.password }"
              placeholder="np. 1983-04-12"
              autocomplete="current-password"
              @blur="validateField('password')"
            />
            <div class="invalid-feedback">{{ errors.password }}</div>
          </div>
          <div v-if="apiError" class="alert alert-danger py-2">{{ apiError }}</div>
          <button type="submit" class="btn btn-primary w-100" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Logowanie…' : 'Zaloguj się' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const form = reactive({ login: '', password: '' })
const errors = reactive({ login: '', password: '' })
const apiError = ref('')
const loading = ref(false)

function validateField(field) {
  if (field === 'login') {
    errors.login = form.login.trim() ? '' : 'Podaj login (imię i nazwisko, np. PiotrKowalski).'
  }
  if (field === 'password') {
    errors.password = form.password.trim() ? '' : 'Podaj hasło (datę urodzenia, np. 1983-04-12).'
  }
}

function validate() {
  validateField('login')
  validateField('password')
  return !errors.login && !errors.password
}

async function handleLogin() {
  apiError.value = ''
  if (!validate()) return

  loading.value = true
  try {
    const { data } = await api.post('/login', form)
    localStorage.setItem('token', data.token)
    router.push('/results')
  } catch (e) {
    apiError.value = e.response?.data?.message ?? 'Błąd logowania. Spróbuj ponownie.'
  } finally {
    loading.value = false
  }
}
</script>

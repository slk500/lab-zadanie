<template>
  <div>
    <nav class="navbar navbar-dark bg-primary px-4">
      <span class="navbar-brand mb-0 h5">Wyniki Badań</span>
      <button class="btn btn-outline-light btn-sm" @click="logout">Wyloguj</button>
    </nav>

    <div class="container py-4">
      <div v-if="loading" class="text-center py-5 text-muted">Ładowanie…</div>
      <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

      <template v-else>
        <div class="card mb-4 shadow-sm">
          <div class="card-header bg-white fw-semibold">Dane Pacjenta</div>
          <div class="card-body">
            <dl class="row mb-0">
              <dt class="col-sm-3">ID</dt>
              <dd class="col-sm-9">{{ patient.id }}</dd>
              <dt class="col-sm-3">Imię</dt>
              <dd class="col-sm-9">{{ patient.name }}</dd>
              <dt class="col-sm-3">Nazwisko</dt>
              <dd class="col-sm-9">{{ patient.surname }}</dd>
              <dt class="col-sm-3">Płeć</dt>
              <dd class="col-sm-9">{{ patient.sex === 'm' ? 'Mężczyzna' : 'Kobieta' }}</dd>
              <dt class="col-sm-3">Data urodzenia</dt>
              <dd class="col-sm-9 mb-0">{{ patient.birthDate }}</dd>
            </dl>
          </div>
        </div>

        <div v-for="order in orders" :key="order.orderId" class="card mb-3 shadow-sm">
          <div class="card-header bg-white fw-semibold">Zlecenie #{{ order.orderId }}</div>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Badanie</th>
                  <th>Wartość</th>
                  <th>Wartość referencyjna</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(result, idx) in order.results" :key="idx">
                  <td>{{ result.name }}</td>
                  <td>{{ result.value }}</td>
                  <td>{{ result.reference }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const patient = ref({})
const orders = ref([])
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const { data } = await api.get('/results')
    patient.value = data.patient
    orders.value = data.orders
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Nie udało się pobrać wyników.'
  } finally {
    loading.value = false
  }
})

function logout() {
  localStorage.removeItem('token')
  router.push('/login')
}
</script>

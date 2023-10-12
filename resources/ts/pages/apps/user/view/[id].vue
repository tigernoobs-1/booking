<script setup lang="ts">
import UserBioPanel from '@/views/apps/user/view/UserBioPanel.vue';
// 👉 Store
const userData = ref()
userData.value = JSON.parse(localStorage.getItem('userData'))
/* userListStore.fetchUser(Number(route.params.id)).then(response => {
 
  console.log(userData.value)
}) */
</script>

<template>
  <VRow v-if="userData">
    <VCol cols="12">
      <UserBioPanel :user-data="userData" />
    </VCol>
  </VRow>
</template>

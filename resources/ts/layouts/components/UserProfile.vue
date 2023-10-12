<script setup lang="ts">
import userRole from '@/config/roleConfig';
import { useUserSession } from '@/pages/userStore';
import { useApi } from '@/plugins/axios';
import router from '@/router';
import { isUserLoggedIn } from '@/router/utils';
import { avatarText } from '@core/utils/formatters';

const isLoggedIn = isUserLoggedIn()
const userSession = useUserSession()
const userData = ref()
const name = ref()
if (isLoggedIn) {
  userData.value = JSON.parse(localStorage.getItem('userData'))

  console.log(userData.value.user_id)
  name.value = userData.value.name
}


const logout = async () => {
  try {
    const api = useApi()
    const response = await api.post('logout')
    localStorage.clear();
    router.push('/')
    location.reload();
  } catch (error: any) {
    if (error.response && error.response.status === 401) {
      localStorage.clear();
      router.push('/login')
    } else {
      console.log(error)
    }
  }
}

const getRoleDescription = (roleId: any) => {
  for (const roleKey in userRole) {
    if (userRole.hasOwnProperty(roleKey)) {
      const role = userRole[roleKey];
      if (role.id === roleId) {
        return role.description;
      }
    }
  }
  return '';
};

console.log(userSession.user)
</script>

<template>
  <div v-if="isLoggedIn">
    <VAvatar class="cursor-pointer" color="primary" variant="tonal">
      <VImg v-if="userData.avatar" :src="userData.avatar" />
      <span v-else class="text-caption font-weight-semibold">
        {{ avatarText(userData.name) }}
      </span>

      <!-- SECTION Menu -->
      <VMenu activator="parent" width="350" location="bottom end" offset="14px">
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VAvatar color="primary" variant="tonal">
                  <VImg v-if="userData.avatar" :src="userData.avatar" />
                  <span v-else class="text-caption font-weight-semibold">
                    {{ avatarText(userData.name) }}
                  </span>
                </VAvatar>
              </VListItemAction>
            </template>

            <VListItemTitle class="text-caption font-weight-semibold">
              {{ name }}
            </VListItemTitle>
            <VListItemSubtitle v-if="userData.role && userData.role !== 'user'">{{ getRoleDescription(userData.role) }}</VListItemSubtitle>
            <VListItemSubtitle style="padding-top: 5px;">{{ userData.company }}</VListItemSubtitle>
          </VListItem>

          <VDivider class="my-2" />

          <!-- 👉 Profile -->
          <VListItem link :to="{ name: 'apps-user-view-id', params: { id: userData.user_id } }">
            <template #prepend>
              <VIcon class="me-2" icon="tabler-user" size="22" />
            </template>

            <VListItemTitle>Profile</VListItemTitle>
          </VListItem>

          <!-- 👉 Settings -->
          <!--  <VListItem link>
            <template #prepend>
              <VIcon class="me-2" icon="tabler-settings" size="22" />
            </template>

            <VListItemTitle>Settings</VListItemTitle>
          </VListItem> -->

          <!-- Divider -->
          <VDivider class="my-2" />

          <!-- 👉 Logout -->
          <VListItem @click="logout">
            <template #prepend>
              <VIcon class="me-2" icon="tabler-logout" size="22" />
            </template>

            <VListItemTitle>Logout</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </div>
  <div v-else>
    <VBtn icon="tabler-login" variant="text" color="black" :to="{ path: 'login' }" />
  </div>
</template>

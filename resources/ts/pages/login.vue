<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'
import { VForm } from 'vuetify/components'
import { useUserSession } from './userStore'
// import axiosIns from '@/plugins/axios'
import userRole from '@/config/roleConfig'
import { useApi } from '@/plugins/axios'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/login-dark.png'
import authV2LoginIllustrationLight from '@images/pages/login-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { requiredValidator } from '@validators'

const authThemeImg = useGenerateImageVariant(authV2LoginIllustrationLight, authV2LoginIllustrationDark, authV2LoginIllustrationBorderedLight, authV2LoginIllustrationBorderedDark, true)

const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

const isPasswordVisible = ref(false)
const userSession = useUserSession()
const username = ref()
const password = ref()
const rememberMe = ref(false)
const refForm = ref<VForm>()
const router = useRouter()
const route = useRoute()
const loadings = ref<boolean[]>([])
const isAlertVisible = ref(false)
const loginMessage = ref()
const isSuccess = ref(true)

async function login(user: any, i: number) {

  const api = useApi()
  try {
    const { data } = await api.post('login', user)

    if (data) {
      localStorage.setItem('userData', JSON.stringify(data))
      localStorage.setItem('accessToken', JSON.stringify(data.token))
      userSession.setToken(data.token)
      userSession.setUser(data)
      loginMessage.value = data.message
      isAlertVisible.value = true
      isSuccess.value = true

      let role = data.role
      if (!role) {
        role = 'Auth'
      }

      const userAbilities = [{
        action: 'read',
        subject: role,
      }]

      if (!role || role === 'Auth') {
        userAbilities[0].subject = 'Auth';
      } else if ([userRole.superAdmin.id, userRole.sportAdmin.id, userRole.facilityAdmin.id].includes(role)) {
        userAbilities[0].subject = ['Auth', 'admin'];
      }

      localStorage.setItem('userAbilities', JSON.stringify(userAbilities))

      setTimeout(() => {
        router.replace(route.query.to ? String(route.query.to) : '/')
      }, 1000)
      console.log('userSession.user', userSession.user)
    } else {
      isAlertVisible.value = true
      loadings.value[i] = false
    }
  } catch (error: any) {
    if (error.response && error.response.status === 401) {
      const data = error.response.data;
      loginMessage.value = data.message;
      isAlertVisible.value = true
      loadings.value[i] = false
      isSuccess.value = false
    } else {
      console.log(error)
    }
  }
}

const handleLogin = (i: number) => {
  // event.preventDefault(); // add this line
  refForm.value?.validate().then(async ({ valid }) => {
    if (valid) {
      loadings.value[i] = true
      const loginUser = {
        username: username.value,
        password: password.value,
      }
      await login(loginUser, i)
    }
  })
}

const alertColor = computed(() => {
  return isSuccess.value ? "success" : "error";
});
</script>

<template>
  <VRow no-gutters class="auth-wrapper">
    <VCol lg="8" class="d-none d-lg-flex">
      <div class="position-relative auth-bg rounded-lg w-100 ma-8 me-0">
        <div class="d-flex align-center justify-center w-100 h-100">
          <VImg max-width="505" :src="authThemeImg" class="auth-illustration mt-16 mb-2" />
        </div>

        <VImg :src="authThemeMask" class="auth-footer-mask" />
      </div>
    </VCol>

    <VCol cols="12" lg="4" class="d-flex align-center justify-center">
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
        <VCardText>
          <VNodeRenderer :nodes="themeConfig.app.logo" class="mb-6" />

          <h5 class="text-h5 font-weight-semibold mb-1">
            Welcome to {{ themeConfig.app.title }}! 👋🏻
          </h5>
          <p class="mb-0">
            Please log in to this system using your staff Active Directory (AD) login credentials.
          </p>
        </VCardText>
        <VCardText>
          <VForm ref="refForm" @submit.prevent="handleLogin(0)">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <VTextField v-model="username" label="Username" type="username" :rules="[requiredValidator]"
                  @click="isAlertVisible = false" />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField v-model="password" label="Password" :rules="[requiredValidator]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible" @click="isAlertVisible = false" />
                <div class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4">
                  <!-- <VCheckbox v-model="rememberMe" label="Remember me" /> -->
                  <RouterLink class="text-primary ms-2 mb-1" :to="{ name: 'register' }">
                    Create an Account
                  </RouterLink>
                  <RouterLink class="text-primary ms-2 mb-1" :to="{ name: 'forgot-password' }">
                    Forgot Password?
                  </RouterLink>
                </div>

                <VBtn block type="submit" :loading="loadings[0]" :disabled="loadings[0]">
                  Login
                </VBtn>
              </VCol>
              <!-- create account -->
              <VCol cols="12">
                <RouterLink class="d-flex align-center justify-center" :to="{ name: 'index' }">
                  <VIcon icon="tabler-chevron-left" class="flip-in-rtl" />
                  <span>Back to Home</span>
                </RouterLink>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
  <!-- flat snackbar -->
  <VSnackbar v-model="isAlertVisible" location="bottom end" variant="flat" :color="alertColor">
    {{ loginMessage }}
  </VSnackbar>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";

.form-error {
  background-color: none;
  block-size: 20px;
  font-size: 15px;
  margin-block-start: 4px;
}
</style>

<route lang="yaml">
meta:
  layout: blank
  action: read
  subject: Auth
  redirectIfLoggedIn: true
</route>

<script setup lang="ts">
import authV2ForgotPasswordIllustrationDark from '@images/pages/auth-v2-forgot-password-illustration-dark.png'
import authV2ForgotPasswordIllustrationLight from '@images/pages/auth-v2-forgot-password-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { useApi } from '@/plugins/axios'
import { VForm } from 'vuetify/components'
import { confirmedValidator, requiredValidator } from '@validators'


const refForm = ref<VForm>()
const email = ref('')
const token = ref()
const password = ref()
const confirmPassword = ref()
const isPasswordVisible = ref(false)
const loginMessage = ref()
const isSuccess = ref(true)
const router = useRouter()
const route = useRoute()
const loadings = ref<boolean[]>([])
const isAlertVisible = ref(false)

const authThemeImg = useGenerateImageVariant(
  authV2ForgotPasswordIllustrationLight,
  authV2ForgotPasswordIllustrationDark,
)

const onSubmit = async () => {
  loadings.value[0] = true
  try {
    const validation = await refForm.value?.validate();

    if (validation?.valid) {
      const formData = {
        token: token.value,
        email: email.value,
        password: password.value,
        password_confirmation: confirmPassword.value

      }
      const api = useApi();
      const { data } = await api.post('reset-password', formData)
      if (data) {
        loginMessage.value = data.message
        isAlertVisible.value = true
        isSuccess.value = true
      }
      setTimeout(() => {
        router.replace(route.query.to ? String(route.query.to) : 'login')
      }, 1000)
    }

  } catch (error: any) {
    if (error.response && error.response.status === 404) {
      console.log('masuk');
      const data = error.response.data;
      loginMessage.value = data.message;
      isAlertVisible.value = true;
      loadings.value[0] = false;  //Note: Ensure `i` is properly defined and accessible in this context.
      isSuccess.value = false;
    } else {
      console.log(error);
    }
  }
}

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  token.value = urlParams.get('token');
  console.log(token.value)
});

const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)
</script>

<template>
  <VRow class="auth-wrapper" no-gutters>
    <VCol md="8" class="d-none d-md-flex">
      <div class="position-relative auth-bg rounded-lg w-100 ma-8 me-0">
        <div class="d-flex align-center justify-center w-100 h-100">
          <VImg max-width="368" :src="authThemeImg" class="auth-illustration mt-16 mb-2" />
        </div>

        <VImg class="auth-footer-mask" :src="authThemeMask" />
      </div>
    </VCol>

    <VCol cols="12" md="4" class="auth-card-v2 d-flex align-center justify-center">
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
        <VCardText>
          <VNodeRenderer :nodes="themeConfig.app.logo" class="mb-6" />
          <h5 class="text-h5 font-weight-semibold mb-1">
            New Password 🔒
          </h5>
          <p class="mb-0">
            Enter Your New Password
          </p>
        </VCardText>

        <VCardText>
          <VForm ref="refForm" @submit.prevent="onSubmit">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <VTextField v-model="email" label="Email" type="email" />
              </VCol>
              <VCol cols="12">
                <VTextField v-model="password" label="Password" :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible" />
              </VCol>

              <VCol cols="12">
                <VTextField v-model="confirmPassword" label="Comfirm Password"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  :rules="[requiredValidator, confirmedValidator(confirmPassword, password)]" />
              </VCol>

              <!-- Reset link -->
              <VCol cols="12">
                <VBtn block type="submit" :loading="loadings[0]" :disabled="loadings[0]">
                  Reset Password
                </VBtn>
              </VCol>

              <!-- back to login -->
              <VCol cols="12">
                <RouterLink class="d-flex align-center justify-center" :to="{ name: 'login' }">
                  <VIcon icon="tabler-chevron-left" class="flip-in-rtl" />
                  <span>Back to login</span>
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
</style>

<route lang="yaml">
meta:
  layout: blank
</route>

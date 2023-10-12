<script setup lang="ts">

import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { VForm } from 'vuetify/components'
import { useApi } from '@/plugins/axios'
import { requiredValidator, CheckingEmail, emailValidator, confirmedValidator } from '@validators'
import authV2RegisterIllustrationBorderedDark from '@images/pages/auth-v2-register-illustration-bordered-dark.png'
import authV2RegisterIllustrationBorderedLight from '@images/pages/auth-v2-register-illustration-bordered-light.png'
import authV2RegisterIllustrationDark from '@images/pages/auth-v2-register-illustration-dark.png'
import authV2RegisterIllustrationLight from '@images/pages/auth-v2-register-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'


const refForm = ref<VForm>()
const company = ref()
const name = ref()
const email = ref()
const password = ref()
const confirmPassword = ref()
const statusList = ref([])
const router = useRouter()
const route = useRoute()
const loadings = ref<boolean[]>([])
const isAlertVisible = ref(false)
const loginMessage = ref()
const isSuccess = ref(true)
const alertColor = ref()


/* const submit = () => {
  refForm.value?.validate().then(async ({ valid }) => {
    if (valid) {
      const formData = {
        name: name.value.toUpperCase(),
        email: email.value,
        company: company.value,
        password: password.value
      }
      const api = useApi()
      const { data } = await api.post('register', formData)
      router.push('/login')
    }
  })
} */

const submit = async () => {
  loadings.value[0] = true
  try {
    const validation = await refForm.value?.validate();

    if (validation?.valid) {
      const formData = {
        name: name.value.toUpperCase(),
        email: email.value,
        company: company.value,
        password: password.value,
        role: 'user'
      }
      const api = useApi();
      const { data } = await api.post('register', formData)
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

const fetchCompany = async () => {
  const api = useApi()
  const { data } = await api.get('company-list')
  statusList.value = data.data.filter((item: { is_ad: number }) => item.is_ad === 1).map((item: { company_name: any }) => item.company_name);
  console.log(statusList.value)

}

onMounted(() => {
  fetchCompany()
})
const imageVariant = useGenerateImageVariant(authV2RegisterIllustrationLight,
  authV2RegisterIllustrationDark,
  authV2RegisterIllustrationBorderedLight,
  authV2RegisterIllustrationBorderedDark, true)

const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

const isPasswordVisible = ref(false)

</script>

<template>
  <VRow no-gutters class="auth-wrapper">
    <VCol md="8" class="d-none d-md-flex">
      <div class="position-relative auth-bg rounded-lg w-100 ma-8 me-0">
        <div class="d-flex align-center justify-center w-100 h-100">
          <VImg max-width="441" :src="imageVariant" class="auth-illustration mt-16 mb-2" />
        </div>

        <VImg class="auth-footer-mask" :src="authThemeMask" />
      </div>
    </VCol>

    <VCol cols="12" md="4" class="auth-card-v2  d-flex align-center justify-center">
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
        <VCardText>
          <VNodeRenderer :nodes="themeConfig.app.logo" class="mb-6" />
          <h5 class="text-h5 font-weight-semibold mb-1">
            Adventure starts here 🚀
          </h5>
          <p class="mb-0">
            Make your app management easy and fun!
          </p>
        </VCardText>

        <VCardText>
          <VForm ref="refForm" @submit.prevent="submit">
            <VRow>
              <VCol cols="12">
                <VSelect v-model="company" :items="statusList" label="Company" :rules="[requiredValidator]" />
              </VCol>
              <!-- Username -->
              <VCol cols="12">
                <VTextField v-model="name" label="Name" :rules="[requiredValidator]" />
              </VCol>

              <!-- email -->
              <VCol cols="12">
                <VTextField v-model="email" label="Email" type="email"
                  :rules="[CheckingEmail(email), emailValidator, requiredValidator]" />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField v-model="password" label="Password" :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible" :rules="[requiredValidator]" />
              </VCol>

               <VCol cols="12">
                  <VTextField v-model="confirmPassword" label="Comfirm Password"
                    :type="isPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    @click:append-inner="isPasswordVisible = !isPasswordVisible"
                    :rules="[requiredValidator, confirmedValidator(confirmPassword, password)]" />
                </VCol>

              <VCol cols="12">
                <VBtn block type="submit" :loading="loadings[0]" :disabled="loadings[0]">
                  Sign up
                </VBtn>
              </VCol>

              <!-- create account -->
              <VCol cols="12" class="text-center text-base">
                <span>Already have an account?</span>
                <RouterLink class="text-primary ms-2" :to="{ name: 'login' }">
                  Sign in instead
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

<script lang="ts" setup>
import { VForm } from 'vuetify/components'
import { isUserLoggedIn } from '@/router/utils'
import { useApi } from '@/plugins/axios'
import { emailValidator, requiredValidator } from '@/@core/utils/validators'

const isLoggedIn = isUserLoggedIn()
const email = ref()
const showFacility = ref(false)
const typeFacility = ref()
const formType = ref()
const details = ref()
const loadings = ref<boolean[]>([])
const items: unknown[] = []
const type = ['Complaint', 'Suggestion']
const refForm = ref<VForm>()
const showSnackBar = ref(false)
const message = ref('')

const mobile = ref<number>()
const password = ref<string>()
const checkbox = ref(false)

if (isLoggedIn) {
  const userdata = JSON.parse(localStorage.getItem('userData'))

  email.value = userdata.email
}

const setFacility = async () => {
  const api = useApi()
  const { data } = await api.get('item')
  const list = data.data
  for (let i = 0; i < list.length; i++)
    items.push(list[i].name)
}

const typeEvent = e => {
  if (e === 'Complaint')
    showFacility.value = true
  else
    showFacility.value = false
  typeFacility.value = ''
}

async function submitApi(fdata, e: number) {
  const api = useApi()
  const { data } = await api.post('complain', fdata)
  if (data) {
    message.value = data.message
    showSnackBar.value = true
    loadings.value[e] = false
    refForm.value?.reset()
  }
}

const submitForm = (i: number) => {
  refForm.value?.validate()
    .then(async ({ valid }) => {
      if (valid) {
        loadings.value[i] = true

        const formData = {
          user: email.value,
          type: formType.value,
          details: details.value,
          facility: typeFacility.value,
        }

        await submitApi(formData, i)
      }
    })
}

onMounted(() => {
  setFacility()
  showSnackBar.value = false
})
</script>

<template>
  <VCard
    class="mb-6"
    title="Leave Your Complaint & Suggestion"
  >
    <VCardText>
      <VForm
        ref="refForm"
        @submit.prevent="submitForm(0)"
      >
        <VRow>
          <!-- 👉 Email -->
          <VCol cols="12">
            <VRow no-gutters>
              <VCol
                cols="12"
                md="3"
              >
                <label for="firstNameHorizontalIcons">Email</label>
              </VCol>

              <VCol
                cols="12"
                md="9"
              >
                <VTextField
                  id="firstNameHorizontalIcons"
                  v-model="email"
                  :rules="[requiredValidator, emailValidator]"
                  prepend-inner-icon="tabler-user"
                  placeholder="Email"
                  persistent-placeholder
                />
              </VCol>
            </VRow>
          </VCol>
          <!-- 👉 Type Form -->
          <VCol cols="12">
            <VRow no-gutters>
              <VCol
                cols="12"
                md="3"
              >
                <label for="mobileHorizontalIcons">Type</label>
              </VCol>

              <VCol
                cols="12"
                md="9"
              >
                <VSelect
                  v-model="formType"
                  :rules="[requiredValidator]"
                  :items="type"
                  label="Type"
                  @update:model-value="typeEvent"
                />
              </VCol>
            </VRow>
          </VCol>

          <!-- 👉 Facility -->
          <VCol
            v-if="showFacility"
            cols="12"
          >
            <VRow no-gutters>
              <VCol
                cols="12"
                md="3"
              >
                <label for="mobileHorizontalIcons">Facility</label>
              </VCol>

              <VCol
                cols="12"
                md="9"
              >
                <VSelect
                  v-model="typeFacility"
                  :items="items"
                  label="Facility"
                />
              </VCol>
            </VRow>
          </VCol>

          <!-- 👉 Details -->
          <VCol cols="12">
            <VRow no-gutters>
              <VCol
                cols="12"
                md="3"
              >
                <label for="emailHorizontalIcons">Details</label>
              </VCol>

              <VCol
                cols="12"
                md="9"
              >
                <VTextarea
                  v-model="details"
                  :rules="[requiredValidator]"
                  label="Details"
                  auto-grow
                />
              </VCol>
            </VRow>
          </VCol>
          <!-- 👉 submit and reset button -->
          <VCol
            offset-md="3"
            cols="12"
            md="9"
            class="d-flex gap-4"
          >
            <VBtn
              :loading="loadings[0]"
              :disabled="loadings[0]"
              type="submit"
            >
              Submit
            </VBtn>
            <VBtn
              color="secondary"
              type="reset"
              variant="tonal"
            >
              Reset
            </VBtn>
          </VCol>

          <!-- 👉 Password -->
          <!--
            <VCol cols="12">
            <VRow no-gutters>
            <VCol
            cols="12"
            md="3"
            >
            <label for="passwordHorizontalIcons">Password</label>
            </VCol>

            <VCol
            cols="12"
            md="9"
            >
            <VTextField
            id="passwordHorizontalIcons"
            v-model="password"
            prepend-inner-icon="tabler-lock"
            type="password"
            placeholder="Password"
            persistent-placeholder
            />
            </VCol>
            </VRow>
            </VCol>
          -->

          <!-- 👉 Checkbox -->
          <!--
            <VCol
            offset-md="3"
            cols="12"
            md="9"
            >
            <VCheckbox
            v-model="checkbox"
            label="Remember me"
            />
            </VCol>
          -->
        </VRow>
      </VForm>
      <VSnackbar
        v-model="showSnackBar"
        location="bottom end"
        variant="flat"
        color="success"
      >
        {{ message }}
      </VSnackbar>
    </VCardText>
  </VCard>
</template>

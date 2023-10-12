<!-- eslint-disable vue/no-mutating-props -->
<script setup lang="ts">
interface Emit {
  (e: 'removeProduct', value: number): void
  /* (e: 'totalAmount', value: number): void */
  (e: 'addItem', value:any) : void
}


const itemName = ref()
const itemCost = ref()
const itemQuantity = ref()

interface Props {
  id: number
  data: {
    item: string
    price: number
    quantity: number
  }
}




const props = defineProps<Props>()
console.log(props.data)

const emit = defineEmits<Emit>()

const saveItem = () => {

  const itemData = {

    item: props.data.item,
    quantity: props.data.quantity,
    price: props.data.price

  }

  emit('addItem', itemData )

}

const removeProduct = () => {
  emit('removeProduct', props.id)
}

</script>

<template>
  <!-- eslint-disable vue/no-mutating-props -->
  <div class="add-products-header mb-2 d-none d-md-flex">
    <VRow class="font-weight-medium px-4">
      <VCol cols="12" md="6">
        <span class="text-sm">
          Item
        </span>
      </VCol>
      <VCol cols="12" md="2">
        <span class="text-sm">
          Price
        </span>
      </VCol>
      <VCol cols="12" md="2">
        <span class="text-sm">
          Quantity
        </span>
      </VCol>
    </VRow>
  </div>

  <VCard flat border class="d-flex flex-row">
    <!-- 👉 Left Form -->
    <div class="pa-6 flex-grow-1">
      <VRow>
        <VCol cols="12" md="6">
          <VTextField v-model="props.data.item"  label="Item"  />
        </VCol>
        <VCol cols="12" md="3" sm="4">
          <VTextField v-model="props.data.price" type="number" label="Price" prefix="RM"  />
        </VCol>
        <VCol cols="12" md="2" sm="4">
          <VTextField v-model="props.data.quantity" type="number" label="Quantity"/>
        </VCol>
      </VRow>
    </div>

    <!-- 👉 Item Actions -->
    <div class="d-flex flex-column justify-space-between border-s pa-1">
      <VBtn icon size="x-small" color="default" variant="text" @click="removeProduct">
        <VIcon size="20" icon="tabler-x" />
      </VBtn>

      <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="20" icon="tabler-settings" @click="saveItem" />
      </VBtn>
    </div>
  </VCard>
</template>

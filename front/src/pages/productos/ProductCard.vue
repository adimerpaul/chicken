<template>
  <div class="row">
    <div class="col-12 flex flex-center">
      <q-btn v-for="category in categories" :key="category.id" class="q-ma-xs"
             color="black" outline :label="category.name" rounded size="10px"
             :icon="category.icon" @click="categoryClick(category)" no-caps></q-btn>
    </div>
    <div class="col-6 col-md-2" v-for="product in products" :key="product.id">
      <q-card class="q-ma-xs cursor-pointer" flat bordered @click="productClick(product)">
        <q-img
          :src="`${$url}../images/${product.image}`"
          spinner-color="white"
          spinner-size="40"
          style="height: 120px;background: linear-gradient(rgba(74,20,140,0.5), rgba(74,20,140,0));"
        >
          <div class="absolute-bottom lobster text-h6"
               style="padding: 5px; background: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.7)); color: white;line-height: 1">
            {{ $filters.capitalize(product.name)}}
            <div class="row items-center">
<!--              <div class="text-bold text-center q-pl-xs">-->
<!--                {{product.stock}}-->
<!--              </div>-->
              <q-space />
              <div class="text-bold text-center q-pr-xs">
                {{product.price}}
                <q-avatar text-color="white" size="20px" color="red">
                  Bs
                </q-avatar>
              </div>
            </div>
          </div>
        </q-img>
      </q-card>
    </div>
  </div>
</template>
<script>
export default {
  name: 'ProductCard',
  props: {
    products: {
      type: Array,
      required: true
    },
    categories: {
      type: Array,
      required: true
    }
  },
  methods: {
    productClick(product) {
      this.$emit('productClick', product)
    },
    categoryClick(category) {
      this.$emit('categoryClick', category)
    }
  }
}
</script>

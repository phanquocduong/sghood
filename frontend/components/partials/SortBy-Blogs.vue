<template>
  <div class="sort-by">
    <div class="sort-by-select">
      <select class="chosen-select-no-single" v-if="categories?.length">
        <option value="">Danh mục mặc định</option>
        <option v-for="cate in categories" :key="cate" :value="cate">
          {{ cate }}
        </option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'

const props = defineProps({
  categories: {
    type: Array,
    default: () => []
  },
  selectedCategory: {
    type: String,
    default: ''
  },
  handleFilter: Function
})

const emit = defineEmits(['update:selectedCategory'])
const internalSelected = ref(props.selectedCategory)

// === Init Chosen plugin ===
const initChosen = () => {
  const $ = window.jQuery
  const $el = $('.chosen-select-no-single')

  if ($el.length === 0) return

  if ($el.data('chosen')) {
    $el.chosen('destroy')
  }

  $el.chosen({ disable_search_threshold: 10 })

  if (props.selectedCategory) {
    $el.val(props.selectedCategory).trigger('chosen:updated')
  }

  $el.off('change').on('change', function (e) {
    const value = $(e.target).val()
    emit('update:selectedCategory', value)
    props.handleFilter?.(value)
  })
}

// === Re-init chosen when categories change ===
watch(() => props.categories, () => {
  nextTick(() => {
    initChosen()
  })
}, { deep: true })

// === Update chosen when selectedCategory changes ===
watch(() => props.selectedCategory, (val) => {
  const $ = window.jQuery
  const $el = $('.chosen-select-no-single')
  if ($el.data('chosen')) {
    $el.val(val).trigger('chosen:updated')
  }
})

onMounted(() => {
  nextTick(() => {
    initChosen()
  })
})
</script>

<style scoped>
.sort-by-select {
  display: flex;
  justify-content: flex-end;
}

.sort-by .chosen-container {
  width: auto !important;
  min-width: 120px;
}

.sort-by .chosen-container-single .chosen-single {
  padding: 6px 12px !important;
  height: auto;
  line-height: normal;
  text-align: right;
  margin-left: -18px !important;
  float: right;
}

.sort-by .chosen-single span {
  text-align: right;
  width: 100%;
}

.sort-by-select select {
  padding: 5px;
}
</style>

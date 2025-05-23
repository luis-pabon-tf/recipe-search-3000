<script setup lang="ts">
const route = useRoute()
const data  = await useFetch('/api/recipes/' + route.params.id)
</script>

<template>
<div>
    <div>
  Recipe name

  name

  description

  ingredients

  steps

  email

  slug
    </div>
</div>
</template>

<script setup lang="ts">
const email = defineModel()
const keyword = defineModel()
const ingredient = defineModel()

async function handleFormSubmit() {
  const res = await useFetch('localhost:8888/api/search', {
    method: 'POST',
    body: {
        'email': email,
        'keyword': keyword,
        'ingredient': ingredient
    }
  })
}
</script>

<template>
    <form @submit="handleFormSubmit">
        <label for="email">Author email:</label><br>
        <input v-model="email" placeholder="exact author email"><br>

        <label for="keyword">Keyword:</label><br>
        <input v-model="keyword" placeholder="name, description, ingredient or step"><br>

        <label for="ingredient">Ingredient:</label><br>
        <input v-model="ingredient" placeholder="partial ingredient match"><br>

        <button type="submit">Search</button>
    </form>
    <NuxtLoadingIndicator />
    <NuxtPage />
</template>

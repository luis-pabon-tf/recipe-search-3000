<script setup lang="ts">
const email = defineModel('email')
const keyword = defineModel('keyword')
const ingredient = defineModel('ingredient')
</script>

<template>
    <label for="email">Author email:</label><br>
    <input type="text" v-model="email" placeholder="exact author email"><br>

    <label for="keyword">Keyword:</label><br>
    <input type="text" v-model="keyword" placeholder="name, description, ingredient or step"><br>

    <label for="ingredient">Ingredient:</label><br>
    <input type="text" v-model="ingredient" placeholder="partial ingredient match"><br>
</template>

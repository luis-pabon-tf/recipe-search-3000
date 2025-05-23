<script setup lang="ts">
const email = ""
const keyword = ""
const ingredient = ""

const headers = [
    'Name',
    'Description',
    'Author',
    'Details'
]

let res: any = null
async function handleFormSubmit() {
  res = await useFetch('http://127.0.0.1:8888/api/search', {
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
    <div>
        <form @submit.prevent="handleFormSubmit">
            <label for="email">Author email:</label><br>
            <input v-model="email" placeholder="exact author email"><br>

            <label for="keyword">Keyword:</label><br>
            <input v-model="keyword" placeholder="name, description, ingredient or step"><br>

            <label for="ingredient">Ingredient:</label><br>
            <input v-model="ingredient" placeholder="partial ingredient match"><br>

            <button type="submit">Search</button>
        </form>
    </div>

    <div>
        <div v-if="res == null">
            Do a search
        </div>
        <div v-else>
            <SimpleTable :headers="headers" :data="res.data">
                <template #column0="{ entry }">
                {{ entry.name }}
                </template>
                <template #column1="{ entry }">
                {{ entry.description }}
                </template>
                <template #column2="{ entry }">
                {{ entry.email }}
                </template>
                <template #column3="{ entry }">
                    <NuxtLink :to="'/recipe/' + entry.slug">View more</NuxtLink>
                </template>
            </SimpleTable>
        </div>
    </div>
    <NuxtLoadingIndicator />
</template>

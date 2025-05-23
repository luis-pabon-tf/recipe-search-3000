<script setup lang="ts">

const email = ref('')
const keyword = ref('')
const ingredient = ref('')
let searchPage = 'http://127.0.0.1:8888/api/search'

const headers = [
    'Name',
    'Description',
    'Details'
]

const { data, execute, error, status } = await useFetch(searchPage, {
    method: 'POST',
    body: {
        'email': email,
        'keyword': keyword,
        'ingredient': ingredient
    },
    immediate: false
})

function paginate(link: string) {
    searchPage = link
    handleFormSubmit()
}

async function handleFormSubmit() {
    execute()
}
</script>

<template>
<div>
    <h1>Search Form</h1>

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
        <div v-if="status === 'idle'">
            No data
        </div>
        <div v-else-if="status === 'pending'">
            Loading recipes...
        </div>
        <div v-else-if="status === 'error'">
            {{ error }}
        </div>
        <div v-else>
            <SimpleTable :headers="headers" :rows="data" @change-page="paginate">
                <template #column0="{ entry }">
                {{ entry.name }}
                </template>
                <template #column1="{ entry }">
                {{ entry.description }}
                </template>
                <template #column2="{ entry }">
                    <NuxtLink :to="'/recipe/' + entry.slug">View more</NuxtLink>
                </template>
            </SimpleTable>
        </div>
    </div>
    <NuxtLoadingIndicator />
</div>
</template>

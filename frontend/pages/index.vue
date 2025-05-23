<script setup lang="ts">

const email = ref('')
const keyword = ref('')
const ingredient = ref('')

const headers = [
    'Name',
    'Description',
    'Details'
]

const { data, execute, status } = await useFetch('http://127.0.0.1:8888/api/search', {
    method: 'POST',
    body: {
        'email': email,
        'keyword': keyword,
        'ingredient': ingredient
    },
    immediate: false
})

async function handleFormSubmit() {
    execute()
}
</script>

<template>
<div>
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
        <div v-else>
            <!-- {{ data }} -->
            <SimpleTable :headers="headers" :rows="data">
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

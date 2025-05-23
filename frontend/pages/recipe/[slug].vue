<script setup lang="js">
const route = useRoute()
const { data, error, status } = await useFetch('http://127.0.0.1:8888/api' + route.path)
</script>

<template>
<div>
    <div v-if="status == 'success'">
        <h2>
            Name: {{ data.data.name }}
        </h2>

        <h2>
            Description: {{ data.data.description }}
        </h2>

        <h2>
            Written By: {{ data.data.author_email }}
        </h2>

        <h2>Ingredients</h2>
        <ul>
            <li v-for="ingredient in data.data.ingredients">
                {{ ingredient.name + ' - ' + ingredient.quantity + ' ' + ingredient.unit_type}}
            </li>
        </ul>

        <h2>Steps</h2>
        <ol>
            <li v-for="step in data.data.steps">
                {{ step.description }}
            </li>
        </ol>
    </div>
    <div v-else-if="status == 'error'">{{ error }}</div>
    <div v-else><NuxtLoadingIndicator /></div>
</div>
</template>

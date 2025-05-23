<!-- original vue version by @lizzieturney -->
<script setup lang="ts">
defineProps([
    'headers',
    'rows'
])
</script>

<template>
    <div>
        <table>
            <tr>
                <th v-for="(header, i) in headers" :key="`${header}${i}`" class="header-item">
                    {{ header }}
                </th>
            </tr>
            <tr v-for="entry in rows.data" :key="`entity-${entry.id}`" class="table-rows">
                <td v-for="(header, i) in headers" :key="`${header}-${i}`">
                    <slot :name="`column${i}`" :entry="entry"></slot>
                </td>
            </tr>
        </table>
    </div>
</template>

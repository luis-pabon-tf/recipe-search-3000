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
            <div v-if="rows.links !== 'undefined'" class="paginator">
                <button :disabled="rows.links.prev == null" @click="$emit('changePage', rows.links.prev)">Previous</button>
                <button :disabled="rows.links.next == null" @click="$emit('changePage', rows.links.next)">Next</button>
            </div>
        </table>
    </div>
</template>

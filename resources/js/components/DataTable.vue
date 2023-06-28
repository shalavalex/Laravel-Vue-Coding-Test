<template>
    <div class="card">
        <form @submit.prevent="searchRepositories">
            <input
                v-model="searchTerm"
                type="text"
                placeholder="Search repository by name"
            />
            <button type="submit">Search</button>
        </form>

        <DataTable
            :value="repositories"
            :paginator="true"
            :rows="perPage"
            :totalRecords="totalRecords"
            :lazy="true"
            :loading="loading"
            @onLazyLoad="loadRepositories"
        >
            <Column field="id" header="ID"></Column>
            <Column field="name" header="Name" :sortable="true"></Column>
            <Column field="full_name" header="Full Name"></Column>
            <Column field="html_url" header="HTML URL"></Column>
            <Column field="language" header="Language"></Column>
            <Column
                field="updated_at"
                header="Activity"
                :sortable="true"
            ></Column>
            <Column field="pushed_at" header="Pushed"></Column>
            <Column
                field="stargazers_count"
                header="Popularity"
                :sortable="true"
            ></Column>
        </DataTable>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import GitRepoService from "../services/GitRepoService";

export default {
    components: {
        DataTable,
        Column,
    },
    setup() {
        const repositories = ref([]);
        const perPage = ref(10);
        const totalRecords = ref(0);
        const searchTerm = ref("");
        const loading = ref(false);

        const loadRepositories = async () => {
            loading.value = true;
            const event = {
                first: (repositories.value.length - 1) * perPage.value,
                sortField: null,
                sortOrder: null,
            };
            const data = await GitRepoService.getRepositories({
                page: event.first / perPage.value + 1,
                perPage: perPage.value,
                sortBy: event.sortField,
                sortOrder: event.sortOrder,
                search: searchTerm.value,
            });
            repositories.value = data.repositories;
            totalRecords.value = data.totalRecords;
            loading.value = false;
        };
        const searchRepositories = async () => {
            loading.value = true;
            let data = await GitRepoService.getRepositories({
                page: 1,
                perPage: perPage.value,
                sortBy: null,
                sortOrder: null,
                search: searchTerm.value,
            });
            repositories.value = data.repositories;
            totalRecords.value = data.totalRecords;
            loading.value = false;
        };

        onMounted(loadRepositories);

        return {
            repositories,
            perPage,
            totalRecords,
            searchTerm,
            loading,
            loadRepositories,
            searchRepositories,
        };
    },
};
</script>

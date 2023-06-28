<template>
    <div class="card">
        <div class="card flex justify-content-center searchCard">
            <form
                @submit.prevent="onSubmit"
                class="flex flex-row gap-2 searchForm"
            >
                <span class="p-float-label">
                    <InputText
                        id="value"
                        v-model="value"
                        type="text"
                        :class="{ 'p-invalid': errorMessage }"
                        aria-describedby="text-error"
                    />
                    <label for="value">Name</label>
                </span>
                <small class="p-error" id="text-error">{{
                    errorMessage || "&nbsp;"
                }}</small>
                <Button type="submit" label="Search repository by name" />
            </form>
            <Toast />
        </div>

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
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import { useField, useForm } from "vee-validate";
import GitRepoService from "../services/GitRepoService";

export default {
    components: {
        DataTable,
        Column,
        InputText,
        Button,
    },
    setup() {
        const { handleSubmit, resetForm } = useForm();
        const { value, errorMessage } = useField("value", validateField);

        const repositories = ref([]);
        const perPage = ref(10);
        const totalRecords = ref(0);
        const loading = ref(false);

        function validateField(value) {
            if (!value) {
                return " ";
            }

            return true;
        }

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
                search: value.value,
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
                search: value.value,
            });
            repositories.value = data.repositories;
            totalRecords.value = data.totalRecords;
            loading.value = false;
        };

        const onSubmit = handleSubmit((values) => {
            if (values.value && values.value.length > 0) {
                searchRepositories();
                resetForm();
            }
        });

        onMounted(loadRepositories);

        return {
            repositories,
            perPage,
            totalRecords,
            errorMessage,
            loading,
            value,
            loadRepositories,
            onSubmit,
        };
    },
};
</script>

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
            :sortField="sortField"
            :sortOrder="sortOrder"
            :paginator="true"
            :rows="perPage"
            :totalRecords="totalRecords"
            :lazy="true"
            :loading="loading"
            tableStyle="min-width: 50rem"
            @page="onPageChange"
            @sort="onSort"
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
        const perPage = ref(5);
        const totalRecords = ref(0);
        const loading = ref(false);
        const sortField = ref(null);
        const sortOrder = ref(null);

        function validateField(value) {
            if (!value) {
                return " ";
            }
            return true;
        }

        const fetchData = async (page, searchValue, sortBy, sortOrder) => {
            try {
                loading.value = true;
                const data = await GitRepoService.getRepositories({
                    page,
                    perPage: perPage.value,
                    sortBy,
                    sortOrder,
                    search: searchValue,
                });
                repositories.value = data.repositories;
                totalRecords.value = data.totalRecords;
            } catch (error) {
                console.error(error);
            } finally {
                loading.value = false;
            }
        };

        const onPageChange = (event) => {
            const { first, rows } = event;
            const page = Math.ceil(first / rows) + 1;
            fetchData(page, value.value, sortField.value, sortOrder.value);
        };

        const searchRepositories = async () => {
            if (value.value && value.value.length > 0) {
                fetchData(1, value.value, sortField.value, sortOrder.value);
            }
        };

        const onSubmit = handleSubmit((values) => {
            if (values.value && values.value.length > 0) {
                searchRepositories();
                resetForm();
            }
        });

        const onSort = (event) => {
            sortField.value = event.sortField;
            sortOrder.value = event.sortOrder;
            fetchData(1, value.value, sortField.value, sortOrder.value);
        };

        onMounted(() => fetchData(1, null));

        return {
            repositories,
            perPage,
            totalRecords,
            errorMessage,
            loading,
            value,
            sortField,
            sortOrder,
            onPageChange,
            onSubmit,
            onSort,
        };
    },
};
</script>

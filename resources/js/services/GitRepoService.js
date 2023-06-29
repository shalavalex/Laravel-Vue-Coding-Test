import axios from "axios";

const baseUrl = "/api/git_repo";

export default {
    async getRepositories(params) {
        const response = await axios.get(`${baseUrl}`, {
            params: {
                page: params.page,
                perPage: params.perPage,
                sortBy: params.sortBy,
                sortOrder: params.sortOrder,
                search: params.search,
            },
        });
        return response.data;
    },
};

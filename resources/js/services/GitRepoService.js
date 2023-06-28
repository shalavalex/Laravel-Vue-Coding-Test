import axios from "axios";

const baseUrl = "/api/git_repo";

export default {
    async getRepositories(params) {
        const response = await axios.get(`${baseUrl}`, {
            params: {
                page: params.page,
                per_page: params.perPage,
                sort: params.sortBy,
                order: params.sortOrder,
                q: params.search,
            },
        });
        return response.data;
    },
};

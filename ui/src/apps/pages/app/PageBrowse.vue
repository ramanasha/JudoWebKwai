<template>
    <v-container v-if="loading">
        <v-layout row justify-space-around>
            <v-flex xs1>
                <v-progress-circular class="text-xs-center" indeterminate color="red" :size="50" :width="7"></v-progress-circular>
            </v-flex>
        </v-layout>
    </v-container>
    <v-container v-else grid-list-xl class="pt-0">
        <v-layout v-if="category">
            <v-flex xs12>
                <h1 class="display-1">{{ category.name }}</h1>
                <div class="category-description">
                    {{ category.description }}
                </div>
            </v-flex>
        </v-layout>
        <v-layout v-if="noPages" row wrap>
            <v-flex xs12>
                {{ $t('no_pages') }}
            </v-flex>
        </v-layout>
        <v-layout v-else row wrap>
            <v-flex v-for="page in pages" :key="page.id" xs12 md6 d-flex>
                <PageSummary :page="page"></PageSummary>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
    import moment from 'moment';
    import PageSummary from './PageSummary.vue';
    import Paginator from '@/components/Paginator.vue';

    import messages from '../lang/PageBrowse';

    export default {
        i18n : {
            messages : messages
        },
        components : {
            PageSummary,
            Paginator
        },
        props : [
            'category_id'
        ],
        data() {
            return {
            };
        },
        computed : {
            loading() {
                return this.$store.getters['pageModule/loading'];
            },
            pages() {
                return this.$store.getters['pageModule/pages'];
            },
            noPages() {
                return !this.pages || this.pages.length == 0;
            },
            category() {
                if (this.category_id) {
                    return this.$store.getters['categoryModule/category'](this.category_id);
                }
                return null;
            }
          },
        mounted() {
            var categories = this.$store.getters['categoryModule/categories'];
            if (categories.length == 0) {
                this.$store.dispatch('categoryModule/browse');
            }
            this.fetchData({
                category : this.category_id
            });
        },
        watch : {
            '$route'() {
                this.fetchData({
                    category : this.$route.params.category_id
                });
            }
        },
        methods : {
            fetchData(payload) {
                this.$store.dispatch('pageModule/browse', payload);
            },
            readPage(offset) {
                console.log(offset);
            }
        }
    };
</script>

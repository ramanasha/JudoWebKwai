<template>
    <v-container v-if="loading">
        <v-layout row justify-space-around>
            <v-flex xs1>
                <v-progress-circular class="text-xs-center" indeterminate color="red" :size="50" :width="7"></v-progress-circular>
            </v-flex>
        </v-layout>
    </v-container>
    <v-container :class="{ 'pa-1' : $vuetify.breakpoint.name == 'xs' }" v-else>
        <v-layout>
            <v-flex xs12 style="padding-top:0px">
                <NewsCard v-if="story" :story="story" :complete="true" @delete="areYouSure(story.id)" />
                <v-alert v-if="!story && !loading" color="error" value="true" icon="fa-exclamation-triangle">
                    {{ $t('not_found') }}
                </v-alert>
            </v-flex>
        </v-layout>
        <v-dialog v-model="showAreYouSure" max-width="290">
            <v-card>
                <v-card-text>
                    <v-layout>
                        <v-flex xs2>
                            <v-icon color="error">fa-bell</v-icon>
                        </v-flex>
                        <v-flex xs10>
                            <div>{{ $t('sure_to_delete') }}</div>
                        </v-flex>
                    </v-layout>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="error" @click="deleteStory">
                        <v-icon left>fa-trash</v-icon>
                        {{ $t('delete') }}
                    </v-btn>
                    <v-btn @click="showAreYouSure = false">
                        <v-icon left>fa-ban</v-icon>
                        {{ $t('cancel') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
    import NewsCard from '../components/NewsCard.vue';

    import messages from '../lang/NewsRead';

    export default {
        i18n : {
            messages
        },
        components : {
            NewsCard
        },
        data() {
            return {
                showAreYouSure : false,
                storyToDelete : null
            }
        },
        computed : {
            loading() {
                return this.$store.getters['newsModule/loading'];
            },
            story() {
                return this.$store.getters['newsModule/story'](this.$route.params.id);
            },
            loading() {
                return this.$store.getters['newsModule/loading'];
            }
        },
        mounted() {
          this.$store.dispatch('newsModule/read', { id : this.$route.params.id })
            .catch((error) => {
              console.log(error);
          });
        },
        methods : {
            areYouSure(id) {
                this.showAreYouSure = true;
                this.storyToDelete = id;
            },
            deleteStory() {
                this.showAreYouSure = false;
                this.$store.dispatch('newsModule/delete', {
                    id : this.storyToDelete
                }).then(() => {
                    this.$router.go(-1);
                });
            }
        }
    };
</script>

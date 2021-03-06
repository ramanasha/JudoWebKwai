<template>
    <v-card :flat="complete" style="flex:1" :to="contentLink">
        <div v-if="complete">
            <v-card-media v-if="story.header_detail_crop" :src="story.header_detail_crop" height="400px">
                <v-container fill-height fluid>
                    <v-layout fill-height>
                        <v-flex align-end flexbox>
                            <span class="pa-2 headline" style="background-color:rgba(255,255,255,0.7)">
                                {{ title }}
                            </span>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-media>
            <v-card-title>
                <div style="width:100%">
                    <div style="position:relative;width:100%">
                        <div style="position:absolute;top:0;right:0;margin-top:-5px">
                            <v-icon v-if="isNew" color="red">fa-star</v-icon>
                        </div>
                        <h3 v-if="!story.header_detail_crop" class="headline mb-0">
                            {{ title }}
                        </h3>
                    </div>
                    <div class="news-mini-meta">
                        <span v-if="category"><router-link :to="{ name: 'news.category', params: { category_id : category.id} }">{{ category.name }}</router-link> &bull; </span>
                        <span v-if="authorName.length > 0">{{ authorName }} &bull; </span>
                        <span v-if="story.publish_date">{{ $t('published', { publishDate : publishDate, publishDateFromNow : publishDateFromNow }) }}</span>
                    </div>
                    <div v-html="summary" style="margin-top:10px" :class="{ 'news-meta' : complete }">
                    </div>
                </div>
            </v-card-title>
        </div>
        <div v-else>
            <v-card-media v-if="story.header_overview_crop" :src="story.header_overview_crop" height="200px" />
            <v-card-title>
                <div style="width:100%">
                    <div style="position:relative;width:100%">
                        <div style="position:absolute;top:0;right:0;margin-top:-5px">
                            <v-icon v-if="isNew" color="red">fa-star</v-icon>
                            <v-icon v-if="content.length > 0">fa-ellipsis-h</v-icon>
                        </div>
                        <h3 class="mb-0" style="width:80%">
                            {{ title }}
                        </h3>
                    </div>
                    <div class="news-mini-meta">
                        <span v-if="category"><router-link :to="{ name: 'news.category', params: { category_id : category.id} }">{{ category.name }}</router-link> &bull; </span>
                        <span v-if="authorName.length > 0">{{ authorName }} | </span>
                        <span v-if="story.publish_date">{{ $t('published', { publishDate : publishDate, publishDateFromNow : publishDateFromNow }) }}</span>
                    </div>
                </div>
            </v-card-title>
            <v-card-text class="pt-0 pb-0">
                <div v-html="summary" style="margin-top:10px" :class="{ 'news-meta' : complete }">
                </div>
            </v-card-text>
        </div>
        <v-divider v-if="complete"></v-divider>
        <v-card-text v-if="complete">
            <div class="news-content" v-html="content"></div>
        </v-card-text>
        <v-card-actions>
            <v-btn v-if="complete" icon :to="{ name : 'news.browse' }" flat>
                <v-icon>view_list</v-icon>
            </v-btn>
            <v-spacer></v-spacer>
            <v-btn v-if="!featured && $isAllowed('update', story)" color="secondary" icon :to="{ name : 'news.update', params : { id : story.id }}" flat>
                <v-icon>fa-edit</v-icon>
            </v-btn>
            <v-btn v-if="!featured && $isAllowed('remove', story)" color="secondary" icon @click="$emit('delete')" flat>
                <v-icon>fa-trash</v-icon>
            </v-btn>
        </v-card-actions>
    </v-card>
</template>

<style>
    .news-mini-meta {
        font-size: 12px;
        color: #999;
    }
    .news-meta {
        color: #999;
    }

    .news-content img {
        max-width: 100%;
    }

    .news-content ul {
        list-style-position: inside;
        margin-bottom: 20px;
    }

    blockquote {
      background: #f9f9f9;
      border-left: 10px solid #ccc;
      margin: 1.5em 10px;
      padding: 0.5em 10px;
      quotes: "\201C""\201D""\2018""\2019";
    }
    blockquote p {
      display: inline;
    }

    .gallery {
        background: #eee;
        column-count: 4;
        column-gap: 1em;
        padding-left: 1em;
        padding-top: 1em;
        padding-right: 1em;
    }
    .gallery .item {
        background: white;
        display: inline-block;
        margin: 0 0 1em;
        width: 100%;
        padding: 1em;
    }

    @media (max-width: 1200px) {
      .gallery {
      column-count: 4;
      }
    }
    @media (max-width: 1000px) {
      .gallery {
      column-count: 3;
      }
    }
    @media (max-width: 800px) {
      .gallery {
      column-count: 2;
      }
    }
    @media (max-width: 400px) {
      .gallery {
      column-count: 1;
      }
    }
</style>

<script>
    import moment from 'moment';
    import find from 'lodash/find';
    import filter from 'lodash/filter';

    import messages from '../lang/NewsCard';

    export default {
        i18n : {
            messages : messages
        },
        props : {
            story : {
                type : Object,
                required : true
            },
            complete : {
                type : Boolean,
                required : true
            },
            featured : {
                type : Boolean,
                default : false
            }
        },
        computed : {
            contentLink() {
                if ( !this.complete && this.content.length > 0) {
                    return {
                        name : 'news.story',
                        params : {
                            id : this.story.id
                        }
                    };
                }
                return null;
            },
            summary() {
                var content = find(this.story.contents, function(o) {
                    return o.locale == 'nl';
                });
                if (content) {
                    return content.html_summary;
                }
                return "";
            },
            content() {
                var content = find(this.story.contents, function(o) {
                    return o.locale == 'nl';
                });
                if (content) {
                    return content.html_content;
                }
                return "";
            },
            title() {
                var content = find(this.story.contents, function(o) {
                    return o.locale == 'nl';
                });
                if (content) {
                    return content.title;
                }
                return "";
            },
            publishDate() {
                var utc = moment.utc(this.story.publish_date, 'YYYY-MM-DD HH:mm:ss');
                return utc.local().format('L');
            },
            publishDateFromNow() {
                var utc = moment.utc(this.story.publish_date, 'YYYY-MM-DD HH:mm:ss');
                return utc.local().fromNow();
            },
            isNew() {
                var utc = moment.utc(this.story.publish_date, 'YYYY-MM-DD HH:mm:ss');
                return moment().diff(utc.local(), 'weeks') < 1;
            },
            authorName() {
                var content = find(this.story.contents, function(o) {
                    return o.locale == 'nl';
                });
                var author = content.user;
                if (author) {
                    return filter([author.first_name, author.last_name]).join(' ');
                }
                return "";
            },
            category() {
                return this.$store.getters['categoryModule/category'](this.story.category.id);
            }
        }
    }
</script>

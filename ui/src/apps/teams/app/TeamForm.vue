<template>
    <v-container fluid>
        <v-card class="mb-5">
            <v-card-title primary-title>
                <h4 class="headline mb-0">{{ $t('team.details') }}</h4>
            </v-card-title>
            <v-card-text>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-text-field name="name"
                                :label="$t('team.form.name.label')"
                                :hint="$t('team.form.name.hint')"
                                v-model="form.team.name"
                                :error-messages="nameErrors"
                                @input="$v.form.team.name.$touch()"
                                required>
                            </v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-select
                                :items="seasons"
                                v-model="form.team.season"
                                @input="$v.form.team.season.$touch"
                                :error-messages="seasonErrors"
                                :label="$t('team.form.season.label')"
                                :hint="$t('team.form.season.hint')">
                            </v-select>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-select
                                :items="teamtypes"
                                v-model="form.team.team_type"
                                @input="$v.form.team.team_type.$touch"
                                :error-messages="teamtypeErrors"
                                :label="$t('team.form.team_type.label')"
                                :hint="$t('team.form.team_type.hint')">
                            </v-select>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-text-field
                                name="remark"
                                :label="$t('team.form.remark.label')"
                                :hint="$t('team.form.remark.hint')"
                                v-model="form.team.remark"
                                @input="$v.form.team.remark.$touch"
                                :error-messages="remarkErrors"
                                textarea>
                            </v-text-field>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-btn color="primary" :disabled="$v.$invalid" @click="submit">{{ $t('submit') }}</v-btn>
                <v-btn v-if="!team" flat @click="clear">{{ $t('clear') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-container>
</template>

<script>
import moment from 'moment';
import Model from '@/js/model';

import { required, numeric } from 'vuelidate/lib/validators';

var initError = function() {
    return {
        name : [],
        season : [],
        teamtype : [],
        remark : []
    }
};
var initForm = function() {
    return {
        team : {
            name : '',
            season : 0,
            team_type : 0,
            remark : ''
        }
    };
}

import messages from '../lang/lang';
import seasonStore from '@/apps/seasons/store';

export default {
    props : {
        team : {
            type : Object
        }
    },
    i18n : {
        messages
    },
    data() {
        return {
            form : initForm(),
            errors : initError()
        }
    },
    computed : {
        error() {
            return this.$store.state.teamModule.status.error;
        },
        seasons() {
            var seasons = this.$store.getters['seasonModule/seasons'].map((season) => ({value : season.id, text : season.name }));
            seasons.unshift({ value : 0, text : '< ' + this.$t('no_season') + ' >'});
            return seasons;
        },
        teamtypes() {
            var types = this.$store.getters['teamModule/types'].map((type) => ({value : type.id, text : type.name }));
            types.unshift({ value : 0, text : '< ' + this.$t('no_type') + ' >'});
            return types;
        },
        nameErrors() {
            const errors = [...this.errors.name];
            if (! this.$v.form.team.name.$dirty) return errors;
            ! this.$v.form.team.name.required && errors.push('Name is required');
            return errors;
        },
        seasonErrors() {
            const errors = [...this.errors.season];
            if (! this.$v.form.team.season.$dirty) return errors;
            return errors;
        },
        teamtypeErrors() {
            const errors = [...this.errors.teamtype];
            if (! this.$v.form.team.team_type.$dirty) return errors;
            return errors;
        },
        remarkErrors() {
            const errors = [...this.errors.remark];
            if (! this.$v.form.team.remark.$dirty) return errors;
            return errors;
        }
    },
    validations : {
        form : {
            team : {
                name : { required },
                season : {},
                team_type : {},
                remark : {}
            }
        }
    },
    created() {
        if (!this.$store.state.seasonModule) {
            this.$store.registerModule('seasonModule', seasonStore);
        }
    },
    mounted() {
        this.$store.dispatch('seasonModule/browse')
            .then(() => {
                this.$store.dispatch('teamModule/browseType')
                    .then(() => {
                        if ( this.team ) this.fillForm(this.team);
                    });
            });
    },
    watch : {
        team(nv) {
            if (nv) {
                this.fillForm(nv);
            }
        },
        error(nv) {
            if (nv) {
                if ( nv.response.status == 422 ) {
                    nv.response.data.errors.forEach((item, index) => {
                        if ( item.source && item.source.pointer ) {
                            var attr = item.source.pointer.split('/').pop();
                            this.errors[attr].push(item.title);
                        }
                    });
                }
                else if ( nv.response.status == 404 ){
                  //this.error = err.response.statusText;
                }
                else {
                  //TODO: check if we can get here ...
                  console.log(nv);
                }
            }
        }
    },
    methods : {
        clear() {
            this.$v.$reset();
            this.form = initForm();
        },
        fillForm(model) {
            this.form.team.name = model.name;
            this.form.team.remark = model.remark;
            if (model.season) {
                this.form.team.season = model.season.id;
            }
            if (model.team_type) {
                this.form.team.team_type = model.team_type.id;
            }
        },
        fillModel(model) {
            model.addAttribute('name', this.form.team.name);
            model.addAttribute('remark', this.form.team.remark);
            if (this.form.team.season) {
                model.addRelation('season', new Model('seasons', this.form.team.season));
            }
            if (this.form.team.team_type) {
                model.addRelation('teamtype', new Model('teamtypes', this.form.team.team_type));
            }
        },
        submit() {
            this.errors = initError();

            if (this.team) { // update
                this.fillModel(this.team);
                this.$store.dispatch('teamModule/update', this.team.serialize())
                    .then(() => {
                        this.$router.push({ name : 'team.read', params : { id : this.team.id }});
                    }).catch(() => {
                        console.log("Error occurred in teamModule/update");
                    });
            } else { // create
                var team = new Model('teams');
                this.fillModel(team);
                this.$store.dispatch('teamModule/create', team.serialize())
                    .then((newTeam) => {
                        this.$router.push({ name : 'team.read', params : { id : newTeam.id }});
                    }).catch(err => {
                    });
            }
        }
    }
};
</script>
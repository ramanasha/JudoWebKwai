import Model from '@/js/JSONAPI/BaseModel';

import moment from 'moment';

export default class Person extends Model {
    resourceName() {
        return 'persons';
    }

    fields() {
        return [
            'lastname',
            'firstname',
            'gender'
        ];
    }

    dates() {
        return {
            'birthdate' : 'YYYY-MM-DD',
            'created_at' : 'YYYY-MM-DD HH:mm:ss',
            'updated_at' : 'YYYY-MM-DD HH:mm:ss'
        }
    }

    computed() {
        return {
            name(person) {
                return person.lastname + ' ' + person.firstname;
            },
            age(person) {
                return moment().diff(person.birthdate, 'years');
            },
            formatted_birthdate(person) {
                if (person.birthdate) {
                    return person.birthdate.locale('nl').format('L');
                }
                return "";
            },
            isMale(person) {
                return person.gender == 1;
            },
            isFemale(person) {
                return person.gender == 2;
            }
        }
    }

    relationships() {
        return {
        };
    }
}

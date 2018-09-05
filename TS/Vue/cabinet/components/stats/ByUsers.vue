<template>
    <b-container>
        <b-row v-for="member in members" :key="member.id" class="user">
            <b-row  class="user-header">
                <b-col cols="9">
                    <b-media>
                        <b-img slot="aside" :src="member.avatar" rounded="circle" width="36"
                               height="36">
                        </b-img>
                        <p>{{member.profile.name}}</p>
                        <p class="text-small-media">
                            <em>{{member.profile.email}}</em>
                        </p>
                    </b-media>
                </b-col>
                <b-col cols="3">
                   <toggler closed
                            v-b-toggle="`coll-${member.id}`"
                            @click.native.capture="toggle"></toggler>
                </b-col>
            </b-row>
            <b-container  class="">
                <b-collapse :id="`coll-${member.id}`">
                    <b-row>
                        <b-col cols="5" class="text text-center border th-1">Курс</b-col>
                        <b-col cols="5" class="text text-center border th-1">Тесты</b-col>
                        <b-col cols="2" class="text text-center border th-1">Экзамен</b-col>
                    </b-row>
                    <b-row>
                        <!------------>
                        <b-col cols="3" class="border th-2">
                            <p class="text-small">Имя</p>
                        </b-col>
                        <b-col cols="1" class=" border th-2">
                            <p class="text-small">Статус</p>
                        </b-col>
                        <b-col cols="1" class=" border th-2">
                            <p class="text-small">Прогресс</p>
                        </b-col>
                        <!------------>
                        <b-col cols="1" class="border th-2">
                            <p class="text-small">Статус</p>
                        </b-col>
                        <b-col cols="2" class="border th-2">
                            <p class="text-small">Дата</p>
                        </b-col>
                        <b-col cols="2" class=" border th-2">
                            <p class="text-small">Средний бал</p>
                        </b-col>
                        <!------------>
                        <b-col cols="2" class=" border th-2">
                            <p class="text-small">Оценка</p>
                        </b-col>
                    </b-row>
                    <b-row v-for="s in member.profile.statistics" :key="s.course.name">
                        <!------------>
                        <b-col cols="3" class="t-cell border">
                            <b-container class="text-small course-cell">
                              <b-row>
                               <b-col cols="1">
                                 <b-img rounded="circle" blank :blank-color="colors(s.course.cat_mnemo)"
                                       width="24"></b-img>
                                  <p class="short_letter">{{s.course.short_letter}}</p>
                               </b-col>
                               <b-col cols="8">
                                       <p>{{s.course.name.split(":")[0]}}</p>
                                       <p class="text-small-media">{{s.course.name.split(":")[1]}}</p>
                               </b-col>
                              </b-row>
                            </b-container>
                        </b-col>
                        <b-col cols="1" class="t-cell border">
                            <p class="text-small">{{s.course_status}}</p>
                        </b-col>
                        <b-col cols="1" class="t-cell border">
                            <p class="text-small">{{s.progress}}</p>
                        </b-col>
                        <!------------>
                        <b-col cols="1" class="t-cell border">
                            <p class="text-small">{{s.test_status}}</p>
                        </b-col>
                        <b-col cols="2" class="t-cell border">
                            <p class="text-small">{{s.period}}</p>
                        </b-col>
                        <b-col cols="2" class="t-cell border">
                            <p class="text-small">{{s.test_score}}</p>
                        </b-col>
                        <!------------>
                        <b-col cols="2" class="  t-cell border">
                            <p class="text-small">{{s.exam_score}}</p>
                        </b-col>
                    </b-row>

                </b-collapse>
            </b-container>
        </b-row>
    </b-container>
</template>

<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';

    @Component
    export default class ManageTasks extends Vue {

        public fields = [
            {
                key: 'course',
                label: 'Курс',
                sortable: true,
            },
            {
                key: 'period',
                label: 'Начато/Завершено',
                sortable: true,
            },
            {
                key: 'course_status',
                label: 'Статус курса',
                sortable: true,
            },
            {
                key: 'progress',
                label: 'Прогресс курса',
                sortable: true,
            },
            {
                key: 'test_status',
                label: 'Статус тестов',
                sortable: true,
            },
            {
                key: 'test_score',
                label: 'Тесты (средний бал)',
                sortable: true,
            },
            {
                key: 'exam_score',
                label: 'Бал за экзамен',
                sortable: true,
            },


        ];

        get members() {
            let members = this.$store.state.company.members;

            members = members.filter(member => {
                return null !== member.profile
            });
            members.forEach(member => {
                let avatar = (member.profile.avatar) ? member.profile.avatar : 'default.png';
                member.avatar = `/public/assets/img/avatars/${avatar}`;
            });

            return members;
        }

        colors(cat_mnemo) {
            let cat_color = "#80b999";
            switch (cat_mnemo) {
                case "ms-excel" :
                case "vba" :
                    cat_color = "#219053";
                    break;
                case "ms-powerpoint" :
                    cat_color = "#d95333";
                    break;
                case "ms-word" :
                    cat_color = "#2b65bb";
                    break;
                case "ms-project" :
                    cat_color = "#379332";
                    break;
                case "ms-onenote" :
                    cat_color = "#7f377a";
                    break;
            }
            return cat_color;
        };

        toggle($e){
            console.log($e.target)
        }
    }

</script>

<style scoped lang="scss">
    .no-padding {
        padding: 0 !important;
        position: relative;
        top: -10px;
        left: 10px;
    }

    .short_letter {
        position: relative;
        color: #fff;
        top: -21px;
        left: 6.3px;
        font-family: Roboto, serif;
        font-weight: bold;
    }

    .text-small {
        text-align: center;
        position: relative;
        font-size: 14px;
        font-style: italic;
        left: -6px;

    }
    .course-cell{
        position: relative;
        left: -20px;
    }

    .text-small-media {
        font-size: 10px;
        position: relative;
        top: -19px;
    }
    .th-1 {
        height: 32px;
        background: #dee2e6;
        padding: 6px;
        font-family: GloberBoldFree,serif;
    }

    .th-2 {
        height: 24px;
        background: #dee2e670;
    }
    .t-cell{
        padding: 5px 10px;
        height: 42px;
        .text-small{
            font-size: 11px;
        }
    }
    .border {
        border: 1px solid #ccd6dc !important
    }
    .breaker{
        height: 3px;
    }

    .full-width {
        margin: 0 -10px;
    }

    .user {
        width: 100%;
        background: #fff;
        border: 1px solid #ccd6dc;
        border-radius: 3px;
        padding: 10px 10px;
        margin: 0px;
        &-header {
            width: 100%;
            height: 48px;
        }
    }
</style>
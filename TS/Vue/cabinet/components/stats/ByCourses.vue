<template>
    <b-container>
        <b-row v-for="purchased in purchased_courses" :key="purchased.course.id" class="user">
            <b-row v-b-toggle="`coll-${purchased.course.id}`" class="user-header">
                <b-col cols="9">
                    <b-row class="c-header">
                        <b-col cols="1">
                        <b-img rounded="circle" blank :blank-color="colors(purchased.course.category.mnemo)"
                               width="36"></b-img>
                        <p class="short_letter">{{purchased.course.short_letter}}</p>
                        </b-col>
                        <b-col cols="2">
                        <h5>{{purchased.course.name.split(":")[0]}}</h5>
                        <p class="text-small">
                            <em>{{purchased.course.name.split(":")[1]}}</em>
                        </p>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col cols="3">
                       <toggler></toggler>
                </b-col>
            </b-row>
            <b-container  class="">
                <b-collapse :id="`coll-${purchased.course.id}`">
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
                    <b-row v-for="member in filter_stats_course(purchased.course.id)" :key="member.id">
                        <!------------>
                        <b-col cols="3" class="t-cell border">
                            <b-container class="text-small course-cell">
                                <b-row>
                                    <b-col cols="1">
                                        <b-img rounded="circle" :src="member.avatar"
                                               width="24"></b-img>
                                       <span class="user-name-span">
                                         <h6 class="">{{member.profile.name}}</h6>
                                         <p class=""><em>{{member.profile.email}}</em></p>
                                       </span>
                                    </b-col>
                                </b-row>
                            </b-container>
                        </b-col>
                        <b-col cols="1" class="t-cell border">
                            <p class="text-small">{{member.statistics[0].course_status}}</p>
                        </b-col>
                        <b-col cols="1" class="t-cell border">
                            <p class="text-small">{{member.statistics[0].progress}}</p>
                        </b-col>
                        <!------------>
                        <b-col cols="1" class="t-cell border">
                            <p class="text-small">{{member.statistics[0].test_status}}</p>
                        </b-col>
                        <b-col cols="2" class="t-cell border">
                            <p class="text-small">{{member.statistics[0].period}}</p>
                        </b-col>
                        <b-col cols="2" class="t-cell border">
                            <p class="text-small">{{member.statistics[0].test_score}}</p>
                        </b-col>
                        <!------------>
                        <b-col cols="2" class="  t-cell border">
                            <p class="text-small">{{member.statistics[0].exam_score}}</p>
                        </b-col>
                    </b-row>

                </b-collapse>
            </b-container>
        </b-row>
    </b-container>
</template>

<script lang="ts">
    import {Component, Vue, Watch} from 'vue-property-decorator';



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

        get purchased_courses(){
            return this.$store.state.company.purchased_courses;
        }

        filter_stats_course(course_id){
            let members = this.members;

                members.forEach( member => {
                    member.statistics = member.profile.statistics.filter( stat =>{
                        return stat.course_id == course_id;
                    });

                });

            return members.filter( member => member.statistics.length != 0);
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
        top: -29px;
        left: 11.2px;
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
    .c-header{
        height: 48px;
        .text-small{
            position:relative;
            top:-10px;
            left:0px
        }
    }
    .user-name-span{
        position: relative;
        left: 30px;
        top: -25px;
        p{
            position:relative;
            font-size:10px;
            top: -10px;
        }
    }
</style>
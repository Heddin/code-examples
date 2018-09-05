<template>
    <b-container fluid class="container-wrapper">
        <b-row class="a-header">
            <b-col cols="1" class="text-center"><span>  </span></b-col>
            <b-col cols="3" class="text-left">Курсы</b-col>
            <b-col cols="2" class="text-right pull-right hidden-sm hidden-xs">Лицензии</b-col>
            <b-col cols="4">Сотрудники</b-col>
        </b-row>
        <div class="a-body">
            <b-row class="a-body_row" v-for="pretest in pretests" :key="pretest.id">
                <b-col cols="1" class="text-center no-padding">
                    <b-img rounded="circle" blank :blank-color="colors(pretest.category.mnemo)"
                           width="35"></b-img>
                    <p class="short_letter">{{pretest.letter}}</p>
                </b-col>
                <b-col cols="3" class="text-left no-padding">
                    <b-media>
                        <p>{{pretest.name.split(",")[1]}}</p>
                        <p class="text-small">{{pretest.name.split(",")[0]}}</p>
                    </b-media>
                </b-col>
                <b-col cols="2" class="text text-right pull-right">
                    <em class="text-muted bold  font-size07">Не ограничено</em>
                </b-col>
                <b-col cols="6" class="empty no-padding"></b-col>
                <div class="target-area" :data-pretest-id=pretest.id @click.capture="makeActive"></div>
            </b-row>
            <b-row class="a-body_row" v-for="purchased in purchased_courses" :key="purchased.course_id">
                <b-col cols="1" class="text-center no-padding">
                    <b-img rounded="circle" blank :blank-color="colors(purchased.course.category.mnemo)"
                           width="35"></b-img>
                    <p class="short_letter">{{purchased.course.short_letter}}</p>
                </b-col>
                <b-col cols="3" class="text-left no-padding">
                    <b-media>
                        <p>{{purchased.course.name.split(":")[0]}}</p>
                        <p class="text-small">{{purchased.course.name.split(":")[1]}}</p>
                    </b-media>
                </b-col>
                <b-col cols="2" class="text text-right pull-right">
                    <em v-if="!purchased.unlim" class="text-muted bold font-size07">{{purchased.license_left}}/{{purchased.count_license}}</em>
                    <em v-else class="text-muted bold  font-size07">Не ограничено</em>
                </b-col>
                <b-col cols="6" class="empty no-padding"></b-col>
                <div class="target-area" :data-course-id=purchased.course.id @click.capture="makeActive"></div>
            </b-row>
            <b-list-group class="u-list">
                <b-list-group-item class="license-left">
                    <b-row>
                        <b-col cols="6">
                            <span class="font-size07">Осталось лицензий:</span>
                            <em v-if="active_object.unlim" class="text font-size07">Не ограничено</em>
                            <em v-else class="text font-size07">{{active_object.license_left}}/{{active_object.count_license}}</em>
                        </b-col>
                        <b-col cols="6">
                            <b-button v-show="!active_object.unlim || active_object.license_left > 0"
                                      size="sm"
                                      variant="info"
                                      class="pull-right license-left_btn"
                                      :href="active_object.add_license_path"
                            >Добавить
                            </b-button>
                        </b-col>
                    </b-row>
                </b-list-group-item>
                <b-list-group-item v-for="member in user_assigned" :key="member.id" class="u-item">
                    <b-row>
                        <b-col cols="6">
                            <b-media>
                                <b-img slot="aside" :src="member.avatar" rounded="circle" width="36"
                                       height="36"></b-img>
                                <p>{{member.profile.name}}</p>
                                <p class="text-small"><em>{{member.profile.email}}</em></p>
                            </b-media>
                        </b-col>
                        <b-col cols="6" class="text-right">
                            <transition name="fade" mode="out-in">
                                <b-button
                                        v-if="!member.assigned && (active_object.license_left > 0 || active_object.unlim)"
                                        size="sm" key="not_assigned"
                                        variant="success"
                                        @click="assignTask(member) ">Назначить
                                </b-button>
                                <b-button v-else-if="member.assigned" size="sm" key="assigned" style="cursor:default"
                                          variant="outline-success"
                                          disabled>Назначен
                                </b-button>
                            </transition>
                        </b-col>
                    </b-row>
                </b-list-group-item>
            </b-list-group>
        </div>
    </b-container>
</template>

<script lang="ts">
    import {Component, Vue, Watch} from 'vue-property-decorator';


    @Component
    export default class ManageTasks extends Vue {

        public active_id: number = 0;
        public active_type: string = "";
        public active_object: any = {};

        public user_assigned: Array<any> = [];

        public needs_activate: boolean = true;

        get purchased_courses() {
            return this.$store.state.company.purchased_courses;
        }

        get assigned_courses() {
            return this.$store.state.company.assigned_courses;
        }

        get assigned_pretests() {
            return this.$store.state.company.assigned_pretests;
        }

        get pretests() {
            return this.$store.state.company.pretests;
        }

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

        mounted() {
            try {

                if (this.needs_activate) {
                    let target = document.querySelector('.target-area');
                    let falseEvent = {target};

                    this.makeActive(falseEvent);
                    this.needs_activate = false;
                }
            } catch (e) {
            }
        }

        updated() {
            if (this.needs_activate) {
                let target = document.querySelector('.target-area');
                let falseEvent = {target};

                this.makeActive(falseEvent);
                this.needs_activate = false;
            }
        };


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

        makeActive($event) {

            let active_data = $event.target.dataset;

            this.render_user_list(active_data);

            let active_row = $event.target.parentNode;
            let prev_row = document.querySelector('.a-body_row__active');

            if (prev_row) {
                prev_row.classList.remove('a-body_row__active');
            }
            active_row.classList.add('a-body_row__active');
        }

        render_user_list(active_data) {

            if (active_data.courseId) {

                this.active_id = Number(active_data.courseId);
                this.active_type = "course";
                this.active_object = this.purchased_courses.find(purchased => purchased.course.id == this.active_id);

                this.user_assigned = [];

                let courses = this.assigned_courses.filter(assigned => {
                    return assigned.course_id == this.active_id;
                });

                for (let member of this.members) {
                    let present = courses.find(course => course.user_id == member.profile.id);

                    if (present) {
                        member.assigned = true;
                        this.user_assigned.push(member);
                    } else {
                        member.assigned = false;
                        this.user_assigned.push(member);
                    }

                }

            } else {

                this.active_id = Number(active_data.pretestId);
                this.active_type = "pretest";
                this.active_object = this.pretests.find(pretest => pretest.id == this.active_id);
                this.active_object.unlim = true;

                this.user_assigned = [];

                let pretests = this.assigned_pretests.filter(assigned => {
                    return assigned.pretest_id = this.active_id;
                });

                for (let member of this.members) {
                    let present = pretests.find(pretest => pretest.user_id == member.profile.id);
                    if (present) {
                        member.assigned = true;
                        this.user_assigned.push(member);
                    } else {
                        member.assigned = false;
                        this.user_assigned.push(member);
                    }

                }
            }
        }

        assignTask(member) {
            if (this.active_object.license_left > 0 || this.active_object.unlim) {
                let payload = {
                    item_id: this.active_id,
                    item_type: this.active_type,
                    member_id: member.id
                };

                member.assigned = true;
                this.active_object.license_left -= 1;

                this.$store.dispatch('userCourseAssign', payload);
            }
        }
    }
</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
    .a-header {
        width: 730px;
        height: 36px;
        font-size: 18px;
        background-color: #ffffff;
        font-family: Roboto, serif;
        padding-top: 10px;
        margin: 15px;
    }

    .empty {
        background: #f7f7f7;
        top: -20px !important
    }

    .a-body {
        width: 730px;
        min-width: 320px;
        margin: 15px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;;
        &_row {
            background-color: #e8e8e8;
            height: 48px;
            padding-top: 16px;
        }
        &_row:nth-child(odd) {
            background-color: #efefef;
        }
        &_row__active {
            background: #fff !important;
            border: 1px solid #e8e8e8;
        }
    }

    .license-left {
        height: 42px;
        @media all and (max-width: 320px){
            width: 268px;
            position: relative;
            left: -11px;
        }
        &_btn {
            position: relative;
            top: -7px;
        }
    }

    .u-list {
        background: #fff;
        border: 1px solid #e8e8e8;
        position: relative;
        @media all and (max-width: 1024px) {
            position: absolute;
            right: -25;
            top:60px;
        }
        @media all and (max-width: 600px) {
            position: relative;
            right: -55px;
            top: -288px;
        }
        top: -200px;
        right: -360px;
        width: 360px;
        .u-item {
            width: 358px;
            @media all and (max-width: 321px){
               width:256px;
            }
            border-radius: 1px;
            height: 55px;
        }
    }

    .container-wrapper {
        position: relative;
        @media all and(max-width: 600px) {
            left: -13vw;
        }
    }

    .target-area {
        position: relative;
        top: -92px;
        left: 10px;
        width: 370px;
        height: 48px;
    }

    .font-size07 {
        font-size: .7em;
    }

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
        font-family: Roboto, serif;
        font-weight: bold;
    }

    .text-small {
        font-size: 10px;
        position: relative;
        top: -19px;
    }
</style>

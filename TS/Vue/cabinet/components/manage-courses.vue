<template>
    <b-container>
        <b-row>
            <span v-for="purchased in pc">
                  <b-card :title="purchased.course.name.split(':')[0]"
                          :sub-title="purchased.course.name.split(':')[1]"
                          :img-src="purchased.img_src"
                          :img-alt="purchased.course.name"
                          style="max-width: 300px"
                          class="course-card"

                  >
                      <p v-if="!purchased.unlim" class="font-size-14px">
                          <span class="text text-left">Количество лицензий: </span>
                          <span class="text text-right pull-right">
                              <em class="text-muted bold">{{purchased.count_license}}/{{purchased.license_left}}</em>
                          </span>
                      </p>
                      <p v-else class="font-size-14px">
                          <span class="text-sm-left">Количество лицензий: </span>
                          <span class="text-sm-right pull-right">
                              <em class="text-muted bold">Не ограничено</em>
                          </span>
                      </p>
                      <p>
                         <b-button size="sm"
                                   to="/manage-tasks"
                                   class="col-8"
                                   variant="success"
                                   title="Назначить прохождение курса сотруднику.">Назначить</b-button>

                         <b-button variant="dark"
                                   to="/manage-stats"
                                   class="col-3 offset-15px"
                                   title="Отчеты и статистика">
                            <img src="/assets/img/chart-ico.png" alt="Статистика и отчеты">
                         </b-button>
                      </p>
                      <p v-if="!purchased.unlim">
                          <b-button size="sm"
                                    class="col-12"
                                    :href="purchased.add_license_path"
                                    variant="info"
                                    title="Увеличить количество доступных лицензий.">
                              Купить лицензии
                          </b-button>
                      </p>
                      <p v-else>
                           <!--todo: не кнопка-->
                          <b-button size="sm" style="cursor:default"
                                    class="col-12"
                                    disabled
                                    variant="outline-info">
                              У Вас Безлимит
                          </b-button>
                      </p>
                  </b-card>
            </span>
        </b-row>
    </b-container>
</template>

<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';

    @Component
    export default class ManageCourses extends Vue {

        get pc() {
            return this.$store.state.company.purchased_courses;
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
    .course-card{
        margin:5px;
        display: inline-block;
    }
    .font-size-14px{
        font-size: 14px;
    }
    .offset-15px{
        margin-left: 15px;
    }
</style>

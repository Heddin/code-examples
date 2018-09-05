<template>
   <span>
    <b-progress v-show="$store.state.isLoading" :value="$store.state.loadProgress"
                :max=100
                height="3px"
                variant="success"
                animated
                >
    </b-progress>
    <b-container fluid>
        <b-row class="">
            <b-nav class="hidden-md hidden-lg moa">
                <b-nav-item>
                        <router-link to="/" class="text text-dark text-center">
                            <i class="icon icon-portfolio" ></i>
                            <span class="hidden-xs">Компанией</span>
                        </router-link>
                </b-nav-item>
                <b-nav-item >
                        <router-link to="/manage-users" class="text text-dark">
                            <i class="icon icon-humans"></i>
                            <span class="hidden-xs">Сотрудники</span>
                        </router-link>
                </b-nav-item>
                <b-nav-item>
                        <router-link to="/manage-courses" class="text text-dark">
                            <i class="icon icon-hat"></i>
                            <span class="hidden-xs">Курсы</span>
                        </router-link>
                </b-nav-item>
                <b-nav-item>
                        <router-link to="/manage-tasks" class="text text-dark">
                            <i class="icon icon-target"></i>
                            <span class="hidden-xs top--6">Назначить</span>
                        </router-link>
                </b-nav-item>
                <b-nav-item>
                        <router-link to="/manage-stats" class="text text-dark">
                            <i class="icon icon-task"></i>
                            <span class="hidden-xs">Статистика</span>
                        </router-link>
                </b-nav-item>
        </b-nav>
            <span class="aside-wrapper">
                  <div id="aside" class="aside-menu hidden-xs hidden-sm">
                <ul class="aside-ul">
                    <li>
                        <a>
                            <router-link to="/">
                                <div class="imgbox"><i class="icon icon-portfolio"></i></div>
                                <span>Управление компанией</span>
                            </router-link>
                        </a>
                    </li>
                    <li>
                        <a>
                            <router-link to="/manage-users">
                                <div class="imgbox"><i class="icon icon-humans"></i></div>
                                <span>Управление сотрудниками</span>
                            </router-link>
                        </a>
                    </li>
                    <li>
                        <a>
                            <router-link to="/manage-courses">
                                <div class="imgbox"><i class="icon icon-hat"></i></div>
                                <span>Приобретенные курсы</span>
                            </router-link>
                        </a>
                    </li>
                    <li>
                        <a>
                            <router-link to="/manage-tasks">
                                <div class="imgbox"><i class="icon icon-target"></i></div>
                                <span>Назначение курсов</span>
                            </router-link>
                        </a>
                    </li>
                    <li>
                        <a>
                            <router-link to="/manage-stats">
                                <div class="imgbox"><i class="icon icon-task"></i></div>
                                <span>Отчеты и статистика</span>
                            </router-link>
                        </a>
                    </li>

                    <hr>
                    <li>
                        <a class="green-hover hidden-sm hidden-xs">
                            <toggle-aside class="ta">Свернуть</toggle-aside>
                        </a>
                    </li>

                </ul>
            </div>
            </span>
            <span class="view-wrapper">
                <router-view/>
            </span>
        </b-row>
    </b-container>
   </span>
</template>

<style lang="scss" scoped>
    router-link {
        cursor: pointer !important;
    }

    .aside-menu {
        width: 280px;
        -webkit-transition: .3s ease-in;
        -moz-transition: .3s ease-in;
        -ms-transition: .3s ease-in;
        -o-transition: .3s ease-in;
        transition: 0.3s ease-in;
        overflow: hidden;
        padding: 18px 0px 18px 0px;
        -webkit-box-shadow: 0px 0px 10px 0px RGBA(0, 0, 0, 0.1);
        -moz-box-shadow: 0px 0px 10px 0px RGBA(0, 0, 0, 0.1);
        box-shadow: 0px 0px 10px 0px RGBA(0, 0, 0, 0.1);
        height: 100vh;
        .aside-ul{
            li{
                a {
                    display: block;
                    padding: 9px 10px 9px 10px;
                    color: #363e43;
                    font-weight: 400;
                    &:hover{
                        font-weight: 700;
                        color: #5da100;
                    }
                }
            }
        }
    }
    .aside-wrapper{ margin-right: 5px;}
    .view-wrapper{
        max-width: 70%;
        width: 100%;
        margin: 0;
        @media all and (max-width: 1023px){
            margin: 0 10%;
            width: 90%;
        }
    }
    .green-hover {
        &:hover {
            color: #5da100 !important;
        }
    }
    .moa{ margin:0 auto}
    .top--6{
        position: relative;
        top: -6px;
    }
    .closed {
        width: 64px !important;
    }
</style>

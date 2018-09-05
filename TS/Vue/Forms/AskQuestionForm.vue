<template>
    <b-card>
        <template slot="header">
            <h3 class="form-header-text">ЗАДАЙТЕ ВОПРОС</h3>
            <div class="form-info-text">Ответ на Ваш вопрос прийдет на указанный email.</div>
        </template>

        <transition name="fade">
            <p class="errBag" v-show="err" ref="errBag"></p>
        </transition>

        <b-form-group horizontal
                      :label-cols="2"
                      breakpoint="md"
                      description="Ваше имя"
                      label="Имя"
                      label-class=""
                      label-for="ask_name"
                      :invalid-feedback="invalidName"
                      :state="nameState"
        >
            <b-form-input  type="text"
                           placeholder="Имя Пользователя"
                           name="name"
                           id="ask_name"
                           title="Желаемое имя ползователя."
                           autocomplete="name"
                           v-model="user_name"
                           ref="nameField"
                           @change="validateName"
            >
            </b-form-input>
        </b-form-group>
        <b-form-group horizontal
                      :label-cols="2"
                      breakpoint="md"
                      description="Ваш Email"
                      label="Email"
                      label-class=""
                      label-for="ask_email"
                      :invalid-feedback="invalidEmail"
                      :state="emailState"
        >
            <b-form-input  type="email"
                           placeholder="example@mail.com"
                           name="email"
                           id="ask_email"
                           title="Email-адресс, на который Вы хотели бы получить ответ."
                           autocomplete="email"
                           v-model="email"
                           ref="emailField"
                           @change="validateEmail"
            >
            </b-form-input>
        </b-form-group>
        <b-form-group horizontal
                      :label-cols="2"
                      breakpoint="md"
                      description="Текст вопроса"
                      label="Ваш Вопрос"
                      label-class=""
                      label-for="msgField"
                      :invalid-feedback="invalidMsg"
                      :state="msgState"
        >
            <b-form-textarea id="msgField"
                          placeholder="..."
                          title="Текст вопроса, на который Вы бы хотели получить ответ."
                          v-model="msgText"
                          class="text-area"
                          @change="validateMsg"
            >
            </b-form-textarea>
        </b-form-group>
        <div class="buttons-line margin-0-25">
            <a  v-show="isLoading  == 0" class="button button-limeade-approx" @click="ask()">Отправить вопрос</a>
            <img v-show="isLoading == 1" src="/assets/img/loaders/green.gif" class="loader" ref="loginLoader">
            <img v-show="isLoading == 2" src="/assets/img/loaders/success.png" class="loader" ref="loginLoader">
        </div>

        <template slot="footer">
            <div class="text-center">
                <div class="sugg">
                    <span>Нет учётной записи?</span>
                    <span class="sugg-link to-reg">Зарегистрируйтесь!</span>
                </div>
            </div>
        </template>
    </b-card>
</template>

<script lang="ts">
    import {Vue,Component} from 'vue-property-decorator'
    import Axios from 'axios'
    import {emailRegExp, FRONT_VALIDATION, COMMON_ERROR} from "./FormConst";
    const CREATE_QUESTION_ROUTE : string = "/create/questions";

    @Component
    export default class AskQuestionForm extends Vue {

        private http = Axios;
        private email: string = "";
        private user_name: string = "";
        private msgText = "";
        private course: number = 0;

        public emailValid: boolean = true;
        public nameValid: boolean = true;
        public msgValid: boolean = true;

        public err: boolean = false;

        get nameState(){
            return this.nameValid;
        }
        get emailState(){
            return this.emailValid;
        }
        get msgState(){
            return this.msgValid;
        }

        get invalidName(){
            if(this.nameValid){
                return "";
            }else{
                return FRONT_VALIDATION.name;
            }
        }
        get invalidEmail(){
            if(this.emailValid){
                return "";
            }else{
                return FRONT_VALIDATION.email;
            }
        }
        get invalidMsg(){
            if(this.msgValid){
                return "";
            }else{
                return FRONT_VALIDATION.msgText;
            }
        }

        public isLoading: number = 0; //0 - not loading; 1 - loading; 2 - complete

        canSubmit() {
            this.validateEmail();
            this.validateName();
            this.validateMsg();


            return this.emailValid && this.nameValid && this.msgValid;
        }

        validateName(){
            this.nameValid = (this.user_name != "") ? true : false;
        }
        validateMsg(){
            this.msgValid = (this.user_name != "") ? true : false;
        }
        validateEmail(){
            this.emailValid = (this.email.match(emailRegExp) && this.email != "" ? true : false)
        }

        handleErrorResponse(data){
            let errBag = <HTMLParagraphElement>this.$refs.errBag;

            this.err = true;

            if(data.common) {
                errBag.innerText = data.common;
                setTimeout(()=>{this.err = false; },3000);
            }else if(data.success){
                errBag.classList.add('_success');
                errBag.innerText = data.success;
                setTimeout(()=>{
                    this.err = false;

                    errBag.classList.remove('_success');
                    let overlay = <HTMLDivElement>document.getElementById('overlay');
                    overlay.click();
                    this.isLoading = 0;
                },5000);
            }
        }

        ask() {
            if (this.canSubmit()) {

                let request: object = {};
                let data: any = "";
                let code: number = 0;

                let courseField = (<HTMLInputElement>document.getElementById('course_id')).value;


                this.course = Number(courseField);

                request = {
                    name: this.user_name,
                    email: this.email,
                    message: this.msgText,
                    course: this.course
                };

                this.isLoading = 1;

                this.http.post(CREATE_QUESTION_ROUTE, request).then(response => {
                    data = response.data;
                    code = response.status;

                    if (code == 200 && (Number(data) == 1)) {
                        this.isLoading = 2;
                        this.handleErrorResponse({success: FRONT_VALIDATION.msgSent})
                    } else {
                        this.handleErrorResponse({common:COMMON_ERROR});
                        this.isLoading = 0;
                    }

                }).catch(err => {
                    data = err.response.data;
                    this.handleErrorResponse({common:COMMON_ERROR});
                    this.isLoading = 0;
                });

            }
        }
    }
</script>

<style scoped lang="scss">
   .text-area{
       min-height: 100px;
   }
   .margin-0-25{
       margin: 0 28%; /*Unexpected, huh? =)*/
       .button{
           cursor: pointer;
       }
   }

   .errBag{
       color: #de6266;
       font-size: 0.9em;
       margin: -10px 0 10px;
       text-align: center;
   }
   ._success{
       @extend .errBag;
       color: #5fa204 !important;
   }

   .loader{
       height:36px;
       width:36px;
       margin: 0 80.125px;
       float:none;
       display:block;
   }
</style>
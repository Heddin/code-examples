<template>
    <b-container>
        <b-modal id="inviteForm"
                 ok-title="Пригласить" ok-variant="success"
                 cancel-title="Отмена" cancel-variant="outline-dark"
                 hide-header
                 @shown="visibilityFix"
                 @ok.prevent="inviteUser"
                 ref="inviteModal"
        >
                <h2 class="form-header-text">Форма приглашения пользователя</h2>
                <span class="form-info-text font-14">
                    Для добавления пользователя, пожалуйста, заполните следующие поля.
                </span>
                <b-row>
                     <b-col cols="12" ref="commonErr"></b-col>
                </b-row>
                <b-row class="">
                <b-col cols="12" offset="1">
                    <label for="inviteEmail" class="form-label">Email</label>
                    <input id="inviteEmail" class="form-input margin-left-25 padding-0-15"
                           placeholder="example@mail.com"
                           type="email"
                           title="Email-адресс пользователя, которого вы хотите пригласить."
                           required
                           v-model="inviteEmail"
                           ref="emailField"
                    >
                </b-col>
                <b-col cols="12" offset="1">
                    <label for="inviteText" class="form-label form-label_long">Сообщение:</label>
                </b-col>
                <textarea id="inviteText" class="form-text-area padding-0-15"
                          placeholder="..."
                          title="Сообщение, которое будет добавлено к пригласительному письму."
                          v-model="inviteText"
                          ref="messageField"
                ></textarea>
            </b-row>
        </b-modal>
        <b-row>
            <b-media>
                <img slot="aside" src="/assets/img/main-content/top-row/mainicon.png" alt="Main Icon">
                <h3>Пригласите нового сотрудника в Вашу команду</h3>
                <!--<p>Вы можете добавить новых пользователей нажав на кнопку "Пригласить".</p>-->
                <b-button size="" variant="success" v-b-modal.inviteForm>Пригласить
                    <span class="fa fa-plus"></span>
                </b-button>
            </b-media>
        </b-row>
        <br>
        <b-row>
            <b-table striped hover small responsive :items="users" :fields="fields" class="u-table" >
                <template slot="avatar" slot-scope="data">
                    <b-img :src="data.value" rounded="circle" width="36" blank-color="#777"></b-img>
                </template>
                <template slot="status" slot-scope="data">
                    <p :class="`text text-${data.value.variant}`">{{data.value.text}}</p>
                </template>
                <template slot="action" slot-scope="data">
                    <b-button-group>
                        <b-button v-show="data.value.status === 0"
                                  size="sm"
                                  variant="secondary"
                                  @click="cancelInvite(data.value.user)"
                        >Отменить
                        </b-button>
                        <b-button v-show="(data.value.status === 0) && (data.value.haveAttempts)"
                                  size="sm"
                                  variant="warning"
                                  @click="reSendInvite(data.value.user)"
                        >Выслать еще раз
                        </b-button>
                        <b-button v-show="(data.value.status === 1)"
                                  size="sm"
                                  variant="danger"
                                  @click="changeStatus(data.value.user)"
                        >Уволить
                        </b-button>
                        <b-button v-show="data.value.status === 2"
                                  size="sm"
                                  variant="success"
                                  @click="changeStatus(data.value.user)"
                        >Восстановить
                        </b-button>
                        <b-button v-show="data.value.status === 2"
                                  size="sm"
                                  variant="outline-danger"
                                  @click="removeUser(data.value.user)"
                        >Удалить запись
                        </b-button>
                    </b-button-group>
                </template>
            </b-table>
        </b-row>
    </b-container>
</template>

<script lang="ts">

    import {Component, Vue} from 'vue-property-decorator';
    import {
        ERROR_CLASS,
        SUCCESS_CLASS,
        INVALID_FIELD_CLASS_COMMON,
        INVALID_FIELD_CLASS,
        INVALID_TEXTAREA_CLASS,
        COMMON_ERROR,
        FRONT_VALIDATION,
        emailRegExp
    } from "../../../Forms/FormConst";

    const STATUS = [
        {text: 'Приглашен', variant: 'warning'},
        {text: 'Активен', variant: 'success'},
        {text: 'Уволен', variant: 'danger'},
    ];

    const CONFIRM = `Вы уверены?
    Это действие нельзя отменить.`;
    const MAX_ATTEMPTS = 3;

    @Component
    export default class ManageUsers extends Vue {

        public inviteEmail: string = "";
        public inviteText: string = "";
        public message: string = "";
        public fields = [
            {key: "avatar", label: ""},
            {key: "name", label: "Имя", sortable: true},
            {key: "email", label: "Email", sortable: true},
            {key: "position", label: "Должность", sortable: true},
            {key: "course_count", label: "К-во курсов", sortable: true},
            {key: "status", label: "Статус", sortable: true},
            {key: "action", label: "Действие", sortable: true},
        ];

        public resend = {
            is: false,
            for: 0
        };

        constructor(opts) {
            super(opts);
        }

        get company(): any {
            return this.$store.state.company;
        }

        get users(): Array<any> {
            let users = [];

            for (let member of this.company.members) {
                users.push({
                    avatar: `/public/assets/img/avatars/${(member.profile) ? member.profile.avatar : 'default.png'}`,
                    name: (member.profile) ? member.profile.name : "",
                    email: member.user_email,
                    position: member.position,
                    course_count: member.courses_count,//
                    status: STATUS[member.status],
                    action: {
                        status: member.status,
                        user: member.id,
                        haveAttempts: (member.invite_attempts <= MAX_ATTEMPTS)},
                });
            }

            return users;
        }


        get invalidTextTemplate(): HTMLElement {

            let p = document.createElement('p');

            p.className = INVALID_FIELD_CLASS;
            p.innerText = this.message;

            return p;
        }

        get canSubmit(): boolean {
            let result: boolean = false;

            let vEmail = this.validateEmail();
            let vMessage = this.validateMsgText();

            result = (vEmail && vMessage);


            if (!result) {
                let data: any = {};

                if (!vEmail) {
                    data['email'] = FRONT_VALIDATION.email;
                }

                if (!vMessage) {
                    data['msgText'] = FRONT_VALIDATION.msgText;
                }

                this.handleErrorResponse(data);
            }


            return result
        }

        visibilityFix(e) {
            e.target.classList.remove('fade');
            e.target.style.zIndex = 3009;
        }

        handleErrorResponse(data: object) {
            let fieldElement: any = null;
            let common: boolean = false;

            for (let field in data) {
                switch (field) {
                    case "email": {
                        fieldElement = this.$refs.emailField;
                        this.message = data[field];
                        common = false;
                    }
                        break;
                    case "msgText" : {
                        fieldElement = this.$refs.messageField;
                        this.message = data[field];
                        common = false;
                    }
                        break;

                    default : {
                        fieldElement = this.$refs.commonErr;
                        this.message = data[field];
                        common = true;
                    }
                }
                this.renderErrMsgElement(fieldElement, common);
            }
        }

        validateEmail() {
            let result = (emailRegExp.exec(this.inviteEmail)) ? true : false;

            this.makeFieldState(this.$refs.emailField, result);

            return result;
        }

        validateMsgText() {
            let result = (this.inviteText != ""
                && this.inviteText != null
                && (typeof this.inviteText !== undefined));
            this.makeFieldState(this.$refs.messageField, result);
            return result;
        }

        clearFieldState(field_ref) {
            field_ref.classList.remove(SUCCESS_CLASS);
            field_ref.classList.remove(ERROR_CLASS);

            let prev = field_ref.previousElementSibling.previousElementSibling;

            if (prev.classList.contains(INVALID_FIELD_CLASS)) {
                prev.parentElement.removeChild(prev);
            }

            let commonErrElement = <HTMLElement>this.$refs.commonErr;
            if (commonErrElement) {
                let childErr = commonErrElement.querySelector("." + INVALID_FIELD_CLASS_COMMON);
                if (childErr) {
                    commonErrElement.removeChild(childErr);
                }
            }

        }

        makeFieldState(field_ref: any, valid: boolean): void {
            if (valid) {
                field_ref.classList.remove(ERROR_CLASS);
                field_ref.classList.add(SUCCESS_CLASS);
            } else {
                field_ref.classList.remove(SUCCESS_CLASS);
                field_ref.classList.add(ERROR_CLASS);
            }
            if (!field_ref.onfocus) {
                field_ref.onfocus = () => {
                    this.clearFieldState(field_ref);
                }
            }
        }

        renderErrMsgElement(fieldElement: any, common: boolean = false) {
            let template = this.invalidTextTemplate;
            if (common) {
                template.className = INVALID_FIELD_CLASS_COMMON;

                if (!fieldElement.querySelector("." + template.className)) {
                    fieldElement.appendChild(template);
                }
            } else {
                let sibling = fieldElement.previousElementSibling.previousElementSibling;

                if (!(sibling && (sibling.classList.contains(INVALID_FIELD_CLASS) || sibling.classList.contains(INVALID_TEXTAREA_CLASS)))) {
                    if (fieldElement.tagName == "TEXTAREA") {
                        template.classList.add(INVALID_TEXTAREA_CLASS);
                    }
                    fieldElement.previousElementSibling.before(template);
                }

            }
        }

        changeStatus(member_id: number) {
            this.$store.dispatch('changeUserStatus', member_id);
        }
        removeUser(member_id : number){
            this.$store.dispatch('removeMember',{member_id:member_id, message:CONFIRM});
        }
        reSendInvite(member_id: number){
            let member = this.company.members.find( member => member_id == member.id);

            this.resend.is = true;
            this.resend.for = member.id;
            this.inviteEmail = member.user_email;
            (<any>this.$refs.inviteModal).show();
        }
        cancelInvite(member_id: number){
            this.$store.dispatch('cancelInvite',{member_id});
        }
        inviteUser() {
            if (this.canSubmit) {
                let payload : {email,description,member_id?} = {
                    email: this.inviteEmail,
                    description: this.inviteText,
                };

                let hideModal = ()=>{
                    (<any>this.$refs.inviteModal).hide();
                    this.inviteEmail = "";
                    this.inviteText = "";
                };

                if(this.resend.is){
                    payload.member_id = this.resend.for;
                    this.$store.dispatch('reSendInvite',payload).then(()=>{
                        hideModal();
                        this.resend = { is: false, for: 0 };
                    });
                }else{
                    this.$store.dispatch('sendInvite', payload).then(hideModal);
                }

            }
        }

    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
    .container {
        padding: 30px 15px;
    }

    .text {
        font-family: "GloberBoldFree", Roboto;
        font-size: 14px;
        font-weight: 400;
        margin: 5px;
    }

    .u-table {
        background: #fff;
    }

    .margin-left-25 {
        margin-left: 25px;
    }

    .font-14 {
        font-size: 14px;
    }

    .padding-0-15 {
        padding: 15px;
    }
</style>

import Vue from 'vue';
import Vuex from 'vuex'

import {API} from "./router";
import Axios from "axios";


Vue.use(Vuex);

const store = {
    state: {
        companyId: (<HTMLInputElement>document.getElementById('cid')).value,
        company: { isPlaceholder:true, members:[], courses:[] },
        loadProgress: 0,
        isLoading: false,
        assigned_users: []
    },
    mutations: {
        setCompany(state, payload) {
            state.company = payload;
        },
        setProgress(state,payload){
             state.isLoading = true;
             state.loadProgress = payload;
             if(state.loadProgress == 100) setTimeout(() => {state.isLoading = false},1000);
        },
        setAssignedUsers(state,payload){
            state.assigned_users = payload;
        }
    },
    actions: {
        loadCompany({commit, state}) {
           if(state.company.isPlaceholder) {
               Axios.post(API.getCompany(state.companyId),null,{
                    onUploadProgress: progressEvent => {
                        let progress = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
                        commit('setProgress',progress)
                    }
               }).then(r => {
                       console.log("@loadCompany:",r.data);
                       commit('setCompany', r.data);
                   }).catch(e => {
                       console.log(e);
                       commit('setCompany',state.company);
                   });
           }else{
               commit('setCompany',state.company);
           }
        },
        updateCompany({commit,state}, payload) {
            Axios.post(API.updateCompany(state.companyId), payload, {
                onUploadProgress: progressEvent => {
                    let progress = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
                    commit('setProgress',progress)
                }
            }).then(r => {
                    console.log("@updateCompany:",r.data);
                    commit('setCompany',r.data)
            }).catch(e => {
                console.log(e);
                commit('setCompany',state.company);
            });
        },
        deleteCompany({commit,state},message){
            if (confirm(message)) {
                Axios.post(API.deleteCompany(state.companyId),null,{
                    onUploadProgress: progressEvent => {
                        let progress = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
                        commit('setProgress',progress)
                    }
                }).then(r => {document.location = r.data})
                  .catch(e=>{
                      console.log(e);
                      commit('setCompany',state.company);
                  });
            }
        },
        sendInvite({commit,state},payload){

            payload.company_id = state.companyId;

            Axios.post(API.sendInvite(),payload,{
                onUploadProgress: progressEvent => {
                    let progress = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
                    commit('setProgress',progress)
                }
            }).then(r => {
                    commit('setCompany',r.data)
                }).catch(e => {
                    console.log(e);
                    commit('setCompany',state.company);
                });
        },
        reSendInvite({commit,state},payload){

            payload.company_id = state.companyId;

            Axios.post(API.reSendInvite(),payload,{onUploadProgress: progressEvent => {
                    let progress = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
                    commit('setProgress',progress)
                }}).then( r => commit('setCompany',r.data))
                .catch( e =>{
                    console.log(e);
                    commit('setCompany',state.company);
                });
        },
        changeUserStatus({commit,state},member_id){
            let data = {
                company_id: state.companyId,
                member_id: member_id
            };

            Axios.post(API.changeStatus(),data,{
                onUploadProgress: progressEvent => {
                    let progress = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
                    commit('setProgress',progress)
                }
            })  .then( r => { commit('setCompany',r.data) })
                .catch(e => {
                    console.log(e);
                    commit('setCompany',state.company);
                });
        },
        removeMember({commit,state},payload){
            if(confirm(payload.message)){
                let data = {
                    member_id : payload.member_id,
                    company_id: state.companyId
                };
                Axios.post(API.removeMember(),data,{
                    onUploadProgress: progressEvent => {
                        let progress = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
                        commit('setProgress',progress)
                    }
                }).then(r => { commit('setCompany',r.data) }).catch(e => {
                    console.log(e);
                    commit('setCompany',state.company);
                });
            }
        },
        cancelInvite({commit,state},payload){
            payload.company_id = state.companyId;
            Axios.post(API.cancelInvite(),payload,{
                onUploadProgress: progressEvent => {
                    let progress = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
                    commit('setProgress',progress);
                }
            }).then( r => commit('setCompany',r.data)).catch( e => {
                console.log(e);
                commit('setCompany',state.company);
            } );
        },
        userCourseAssign({commit,state},payload){
            payload.company_id = state.companyId;
            Axios.post(API.assignTask(),payload,{
                onUploadProgress: progressEvent => {
                    let progress = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
                    commit('setProgress',progress);
                }
            }).then( r => commit('setCompany',r.data))
                 .catch(e => {console.log(e); commit('setCompany',state.company)})
        },
    }
};

export default new Vuex.Store(<any>store)

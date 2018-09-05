<template>
	<div class="div">
		<template v-for="(user, index) in usersWithProfile">
			<div :class="['user', isActive(user) ? 'user-collapsed' : null] ">
				<img :src="'/assets/img/avatars/'+user.profile.avatar" alt="User avatar">
				<p>
					<span class="name">{{ user.profile.name }}</span> <span class="position">{{ user.profile.position }}</span>
				</p>
				<p class="num-courses">Количество курсов: {{ countResults(user) }}</p>
				<div :class="[ 'get', isActive(user) ? 'get-collapse' : 'get-open' ]" @click="toggle(user)">
					<span class="action">{{ isActive(user) ? 'свернуть' : 'развернуть' }}</span>
					<div class="img"></div>
				</div>
			</div>

			<div v-show="isActive(user)" class="user-courses">
				<b-table class="courses" :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" :stacked="'md'"
				         :fields="fields" :items="user.profile.courses" :striped="true">
					<!-- Name of course -->
					<template slot="name" slot-scope="data">
						{{ data.item.name }}
					</template>
				</b-table>
			</div>
		</template>
	</div>
</template>

<script>
    export default {
    	name: 'ByUsers',
        data: function()
        {
        	return {
		        sortBy: 'active',
		        sortDesc: true,
		        fields: [
			        {
				        key: 'name',
				        label: 'Курс',
				        sortable: false
			        },
			        {
				        key: 'status',
				        label: 'Статус',
				        sortable: false
			        },
			        {
				        key: 'active',
				        label: 'Прогресс',
				        sortable: false
			        },
			        {
				        key: 'period',
				        label: 'Период',
				        sortable: false
			        },
			        {
				        key: 'tests_status',
				        label: 'Статус тестов',
				        sortable: false
			        },
			        {
				        key: 'test_results',
				        label: 'Балл за тесты',
				        sortable: false
			        },
			        {
				        key: 'exam_results',
				        label: 'Балл за экзамен',
				        sortable: false
			        }
		        ],
        		active: []
            };
        },
	    methods:
	    {
	    	// Count number of user results.
	        countResults(user)
	        {
		        return user.profile ? user.profile.courses.length+user.preliminary_tests.length : 0;
	        },

		    toggle(user)
		    {
		    	if(this.isActive(user)){
		    		let index = this.active.findIndex(function(active){
		    			return active === user.user_id;
				    });
					this.active.splice(index, 1);
			    }else{
				    this.active.push(user.user_id);
			    }
		    },

		    isActive(user)
		    {
				return this.active.includes(user.user_id);
		    }
	    },
        computed:
        {
        	// All users that have profile.
        	usersWithProfile: function()
	        {
	        	return this.users.filter(function(user){
	        		return user.profile !== null;
		        });
	        },
	        // All users.
            users: function()
            {
            	return this.$store.state.company.members;
            }
        }
    }
</script>

<style>
</style>
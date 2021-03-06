<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => 'web'], function () {
    Route::auth();
    Route::get('/', 'HomeController@index');

	Route::group(['middleware' => ['auth']], function () {
		Route::get('account', 'HomeController@account');
		Route::post('account/{users}', [
			'as' => 'account',
			'uses' => 'HomeController@postAccount'
		]);
	});

    Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth', 'role:admin']], function () {
		Route::resource('company-categories', 'CompanyCategoriesController');
		Route::resource('job-categories', 'JobCategoriesController');
		Route::resource('contract-types', 'ContractTypesController');
		Route::resource('occupations', 'OccupationsController');
		Route::resource('profiles', 'ProfilesController');
		Route::resource('geo-locations', 'GeoLocationsController', ['only' => ['index', 'store', 'edit']]);
		Route::resource('skills', 'SkillsController');
		Route::resource('activities', 'ActivitiesController');
		Route::resource('admins', 'AdminsController');
		Route::resource('parameters', 'ParametersController');

		Route::get('registers', ['as' => 'admin.registers', 'uses' => 'RegistersController@index']);
		Route::delete('registers/{resumes}', [
			'as' 	=> 'registers.destroy',
			'uses' 	=> 'RegistersController@destroy'
		]);

		Route::post('registers/{users}/active', ['as' 	=> 'registers.active', 'uses' 	=> 'RegistersController@active']);

		Route::get('applications', ['as' => 'admin.applications.index', 'uses' => 'ApplicationsController@index']);
		Route::get('applications/{jobs}', ['as' => 'admin.applications.show', 'uses' => 'ApplicationsController@show']);
		Route::post('applications/{jobs}/select', ['as' => 'admin.applications.select', 'uses' => 'ApplicationsController@select']);

		Route::get('assists', ['as' => 'admin.assists.index', 'uses' => 'AssistsController@index']);
		Route::get('assists/{jobseekers}', ['as' => 'admin.assists.show', 'uses' => 'AssistsController@show']);
		Route::post('assists/{jobseekers}', ['as' => 'admin.assists.store', 'uses' => 'AssistsController@store']);
		Route::post('assists/{jobseekers}/activities/{activities}', ['as' => 'admin.assists.update', 'uses' => 'AssistsController@update']);
		Route::post('assists/{jobseekers}/activities/{activities}/delete', ['as' => 'admin.assists.delete', 'uses' => 'AssistsController@delete']);

		Route::resource('companies', 'CompaniesController', ['only' => ['index']]);
		Route::post('companies/{companies}/active', [
			'as' 	=> 'companies.active',
			'uses' 	=> 'CompaniesController@active'
		]);
		Route::delete('companies/{companies}', [
			'as' 	=> 'companies.destroy',
			'uses' 	=> 'CompaniesController@destroy'
		]);

		Route::get('stats', [
			'as'	=> 'stats',
			'uses'  => 'StatsController@index'
		]);

		Route::get('stats/jobseekers', [
			'as'	=> 'stats.jobseekers',
			'uses'  => 'StatsController@jobseekers'
		]);

		Route::get('stats/companies', [
			'as'	=> 'stats.companies',
			'uses'  => 'StatsController@companies'
		]);

		// Route::resource('jobs', 'JobsController', ['only' => ['index', 'show']]);

		Route::get('users/{users}/edit', [
			'as' => 'users.edit',
			'uses' => 'AdminController@editAccount'
		]);

		Route::post('users/{users}', [
			'as' => 'users.update',
			'uses' => 'AdminController@postEditAccount'
		]);
	});

	Route::group(['namespace' => 'Portal'], function () {
		//Jobseekers
		Route::group(['middleware' => ['auth', 'role:jobseeker;admin']], function () {
			Route::resource('resumes', 'ResumesController', ['except' => ['destroy']]);
			Route::resource('studies', 'StudiesController', ['only' => ['destroy']]);
			Route::resource('experiences', 'ExperiencesController', ['only' => ['destroy']]);
			Route::get('resumes/{resumes}/applications', [
				'as' 	=> 'resumes.applications',
				'uses' 	=> 'ResumesController@applications'
			]);
			Route::post('companies/{companies}/jobs/{jobs}/apply', [
				'as' 	=> 'companies.jobs.store-apply',
				'uses' 	=> 'CompaniesJobsController@postApply'
			]);
		});

		Route::group(['middleware' => ['auth', 'role:jobseeker;employer;admin']], function () {
			Route::get('companies/{companies}/jobs/{jobs}/apply', [
				'as' 	=> 'companies.jobs.apply',
				'uses' 	=> 'CompaniesJobsController@apply'
			]);
		});

		Route::group(['middleware' => ['auth', 'role:jobseeker']], function () {
			Route::get('my-resume', [
				'as' 	=> 'jobseeker.resume',
				'uses'	=> 'ResumesController@myResume'
			]);
			Route::get('my-applications', [
				'as' 	=> 'jobseeker.resume.applications',
				'uses'	=> 'ResumesController@myApplications'
			]);
		});

		//Empolyers
		Route::group(['middleware' => ['auth', 'role:employer;admin']], function () {
			Route::resource('companies', 'CompaniesController', ['only' => ['edit', 'update']]);
			Route::resource('companies.jobs', 'CompaniesJobsController', ['except' => ['index', 'destroy']]);
		});

		Route::group(['middleware' => ['auth', 'role:employer;admin']], function () {
			Route::get('my-company', [
				'as' 	=> 'employer.company',
				'uses'	=> 'CompaniesController@myCompany'
			]);
			Route::get('my-jobs', [
				'as' 	=> 'employer.jobs',
				'uses'	=> 'CompaniesController@myJobs'
			]);
			Route::get('companies/{companies}/applications', [
				'as' 	=> 'companies.applications',
				'uses' 	=> 'CompaniesJobsController@applications'
			]);
			Route::get('companies/{companies}/jobs/{jobs}/applications', [
				'as' 	=> 'companies.jobs.applications',
				'uses' 	=> 'CompaniesJobsController@jobApplications'
			]);
			Route::get('companies/{companies}/jobs/{jobs}/applications/{application}', [
				'as' 	=> 'companies.jobs.applications.show',
				'uses' 	=> 'CompaniesJobsController@resumeApplications'
			]);
			Route::post('companies/{companies}/jobs/{jobs}/accept-application', [
				'as' 	=> 'companies.jobs.accept-application',
				'uses' 	=> 'CompaniesJobsController@acceptJobApplication'
			]);
		});

		//All Users
		Route::resource('resumes', 'ResumesController', ['only' => ['index', 'show']]);
		Route::post('search/resumes', [
			'as' 	=> 'resumes.search',
			'uses' 	=> 'ResumesController@search'
		]);
		Route::resource('jobs', 'JobsController', ['only' => ['index', 'show']]);
		Route::get('search/jobs', [
			'as' 	=> 'jobs.search',
			'uses' 	=> 'JobsController@search'
		]);

		Route::resource('companies', 'CompaniesController', ['only' => ['show']]);

		/*Route::post('search/companies', [
			'as' 	=> 'companies.search',
			'uses' 	=> 'CompaniesController@search'
		]);*/
		Route::resource('companies.jobs', 'CompaniesJobsController', ['only' => ['index', 'show']]);

		/*Route::post('search/companies/{companies}/jobs', [
			'as' 	=> 'companies.jobs.search',
			'uses' 	=> 'CompaniesJobsController@search'
		]);*/

		Route::get('terms', function(){
			return view('portal.terms');
		});

	});

	Route::group(['namespace' => 'Ajax', 'prefix' => 'ajax'], function() {
		Route::get('occupations', ['as' => 'ajax.occupations', 'uses' => 'AjaxController@occupations']);
	});

	Route::group(['namespace' => 'Validations', 'prefix' => 'validations'], function (){
		Route::post('register', [
			'as' 	=> 'validations.register',
			'uses' 	=> 'RegisterController@validation'
		]);
	});


});



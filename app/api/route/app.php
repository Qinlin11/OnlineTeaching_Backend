<?php
namespace app\api\route;

use think\facade\Route;

Route::group('', function (){
    Route::post('user/login', 'api/UserController/login');
    Route::post('user/register', 'api/UserController/register');
    Route::post('user/logout', 'api/UserController/logout');
    Route::get('user/search', 'api/UserController/searchUser');
    Route::get('user/all', 'api/UserController/getAllUsersExceptSelf');
});

Route::group(function(){
    Route::post('seminar/create',  'api/SeminarController/createSeminar');
    Route::post('seminar/add',  'api/SeminarController/addSeminarMember');
    Route::post('seminar/delete',  'api/SeminarController/deleteSeminarMember');
    Route::get('seminar/all',  'api/SeminarController/getAllSeminarMember');
    Route::post('seminar/edit',  'api/SeminarController/editSeminar');
    Route::get('seminar/user',  'api/SeminarController/getUserSeminar');
    Route::get('seminar/delete/:id',  'api/SeminarController/deleteSeminar');
    Route::post('seminar/phone',  'api/SeminarController/addMemberSeminar');
})->middleware(\thans\jwt\middleware\JWTAuth::class);


Route::group(function (){
    Route::post('seminar/upload', 'api/ResourceController/groupResource');
    Route::post('uploadImg', 'api/ResourceController/uploadImg');
    Route::get('file/all', 'api/ResourceController/getAllUploadFile');
    Route::get('file/delete/:resource_id', 'api/ResourceController/deleteGroupFile');
})->allowCrossDomain();

Route::group(function (){
    Route::post('richText/upload', 'api/CollaborativeController/uploadRichText');
    Route::get('richText/all', 'api/CollaborativeController/getAllRichText');
    Route::get('richText/one/:id',  'api/CollaborativeController/getOneRichText');
    Route::get('richText/delete/:id',  'api/CollaborativeController/deleteRichText');
    Route::post('richText/update', 'api/CollaborativeController/updateRichText');
    Route::get('richText/search', 'api/CollaborativeController/searchRichText');
})->allowCrossDomain();

Route::group(function (){
    // 修改密码
    Route::post('account/password', 'api/AccountController/changePassword');

    // 上传头像
    Route::post('account/avatar', 'api/AccountController/uploadAvatar');

    // 修改基本信息
    Route::post('account/basic_info', 'api/AccountController/updateBasicInfo');

    //修改手机号
    Route::post('account/phone', 'api/AccountController/updatePhone');
})->middleware(\thans\jwt\middleware\JWTAuth::class);


// 资源库
Route::group('resource_pool', function () {
    // 获取资源库列表
    Route::get('all', 'ResourceController/index');
    // 创建资源
    Route::post('create', 'ResourceController/create');
    // 获取指定资源类型列表
    Route::get('type/:resource_type', 'ResourceController/showTypeResources');
    // 删除资源
    Route::get('delete/:resource_id', 'ResourceController/delete');
    // 获取资源详情
    Route::get('info/:resource_id', 'ResourceController/info');
    // 搜索资源库资源
    Route::get('search', 'ResourceController/search');
    // 更新资源
})->allowCrossDomain();

// 我的空间目录
Route::group('personal_space', function () {
    // 获取目录列表
    Route::get('index', 'DirectoryController/index');
    // 创建目录
    Route::post('create', 'DirectoryController/create');
    // 删除目录
    Route::get('delete/:directory_id', 'DirectoryController/delete');
    // 更新目录
    Route::post('update/:directory_id', 'DirectoryController/update');   
})->middleware(\thans\jwt\middleware\JWTAuth::class)->allowCrossDomain();

// 我的空间资源
Route::group('personal_space/resource', function () {
    // 单个目录下某个类型的资源
    Route::get('type/:directory_id/:resource_type', 'PersonResourceController/index');
    // 单个资源信息
    Route::get('info/:resource_id', 'PersonResourceController/show');
    // 创建资源
    Route::post('create/:directory_id', 'PersonResourceController/create');
    // 删除资源
    Route::get('delete/:resource_id', 'PersonResourceController/delete');
    // 更新目录
})->middleware(\thans\jwt\middleware\JWTAuth::class)->allowCrossDomain();

// 作业
Route::group('personal_space/activity/assignment', function () {
    // 作业列表
    Route::get('all/:directory_id', 'AssignmentController/index');
    // 单个作业信息
    Route::get('info/:assignment_id', 'AssignmentController/show');
    // 创建作业
    Route::post('create/:directory_id', 'AssignmentController/create');
    // 删除作业
    Route::get('delete/:assignment_id', 'AssignmentController/delete');
    // 搜索资源库资源
    Route::get('search/:directory_id', 'AssignmentController/search');
    // 更新作业
    
})->allowCrossDomain();

// 写作
Route::group('personal_space/activity/writing', function () {
    // 获取写作活动列表
    Route::get('all/:directory_id', 'WritingController/index');
    // 获取写作活动详情
    Route::get('info/:writing_id', 'WritingController/show');
    // 创建写作活动
    Route::post('create/:directory_id', 'WritingController/create');
    // 删除写作活动
    Route::get('delete/:writing_id', 'WritingController/delete');
    // 更新写作
    
})->middleware(\thans\jwt\middleware\JWTAuth::class)->allowCrossDomain();

// 讨论
Route::group('personal_space/activity/discuss', function () {
    // 获取讨论列表
    Route::get('all/:directory_id', 'DiscussController/index');
    // 编辑讨论
    Route::get('info/:discuss_id', 'DiscussController/show');
    // 创建讨论
    Route::post('create/:directory_id', 'DiscussController/create');
    // 删除讨论
    Route::get('delete/:discuss_id', 'DiscussController/delete');
    // 更新目录
    
})->middleware(\thans\jwt\middleware\JWTAuth::class)->allowCrossDomain();

// 投票
Route::group('personal_space/activity/vote', function () {
    // 获取投票列表
    Route::get('all/:directory_id', 'VoteController/index');
    // 获取投票详情
    Route::get('info/:vote_id', 'VoteController/show');
    // 创建投票
    Route::post('create/:directory_id', 'VoteController/create');
    // 删除投票
    Route::get('delete/:vote_id', 'VoteController/delete');
    // 更新投票
    
})->middleware(\thans\jwt\middleware\JWTAuth::class)->allowCrossDomain();

// 后台管理
Route::group('', function () {
    Route::get('showalluser', 'UserAdminController/showalluser');
    Route::get('deleteuser', 'UserAdminController/deleteuser');
    Route::get('searchuser', 'UserAdminController/searchuser');
    Route::post('updateuser', "UserAdminController/updateuser");
    
    Route::get('newdynamic', 'DynamicController/newdynamic');
    
})->allowCrossDomain();



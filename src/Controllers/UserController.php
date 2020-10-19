<?php

namespace SmallRuralDog\Admin\Controllers;

use SmallRuralDog\Admin\Components\Attrs\SelectOption;
use SmallRuralDog\Admin\Components\Form\Input;
use SmallRuralDog\Admin\Components\Form\Select;
use SmallRuralDog\Admin\Components\Form\Upload;
use SmallRuralDog\Admin\Components\Grid\Avatar;
use SmallRuralDog\Admin\Components\Grid\Tag;
use SmallRuralDog\Admin\Facades\Admin;
use SmallRuralDog\Admin\Form;
use SmallRuralDog\Admin\Grid;
use SmallRuralDog\Admin\Layout\Column;
use SmallRuralDog\Admin\Layout\Row;

class UserController extends AdminController
{

    protected function grid()
    {

        $userModel = config('admin.database.users_model');
        $grid = new Grid(new $userModel());
        $grid
            ->quickSearch(['name', 'username'])
            ->quickSearchPlaceholder("用户名 / 名称")
            ->pageBackground()
            ->defaultSort('id', 'asc')
            ->selection()
            ->stripe(true)->emptyText("暂无用户")
            ->perPage(10)
            ->autoHeight();

        $grid->column('id', "ID")->width(80);
        $grid->column('avatar', '头像')->width(80)->align('center')->component(Avatar::make());
        $grid->column('username', "用户名");
        $grid->column('name', '用户昵称');
        $grid->column('roles.name', "角色")->component(Tag::make()->effect('dark'));
        $grid->column('created_at');
        $grid->column('updated_at');

        return $grid;
    }

    protected function form()
    {

        $userModel = config('admin.database.users_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');
        $form = new Form(new $userModel());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');


        $form->item('avatar', '头像')->component(Upload::make()->avatar()->path('avatar')->uniqueName());
        $form->item('phone', '电话号码')->component(Input::make()->showWordLimit()->maxlength(20));
        $form->row(function (Row $row, Form $form) use ($userTable, $connection) {
            $row->column(8, $form->rowItem('username', '用户名')
                ->serveCreationRules(['required', "unique:{$connection}.{$userTable}"])
                ->serveUpdateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"])
                ->component(Input::make())->required());
            $row->column(8, $form->rowItem('name', '名称')->component(Input::make()->showWordLimit()->maxlength(20))->required());
        });

        $form->row(function (Row $row, Form $form) {
            $row->column(8, $form->rowItem('password', '密码')->serveCreationRules(['required', 'string', 'confirmed'])->serveUpdateRules(['confirmed'])->ignoreEmpty()
                ->component(function () {
                    return Input::make()->password()->showPassword();
                }));

            $row->column(8, $form->rowItem('password_confirmation', '确认密码')
                ->copyValue('password')->ignoreEmpty()
                ->component(function () {
                    return Input::make()->password()->showPassword();
                }));
        });
        $form->item('roles', '角色')->component(Select::make()->block()->multiple()->options($roleModel::all()->map(function ($role) {
            return SelectOption::make($role->id, $role->name);
        })->toArray()));
        $form->item('permissions', '权限')->component(Select::make()->clearable()->block()->multiple()->options($permissionModel::all()->map(function ($role) {
            return SelectOption::make($role->id, $role->name);
        })->toArray()));

        $form->saving(function (Form $form) {
            if ($form->password) {
                $form->password = bcrypt($form->password);
            }
        });

        $form->deleting(function (Form $form, $id) {
            if (\Admin::user()->id == $id || $id == 1) {
                return \Admin::responseError("删除失败");
            }
        });
        return $form;
    }

    /**
     * 个人资料编辑表单
     * @return Form
     */
    public function personal()
    {
        $userModel = config('admin.database.users_model');
        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');
        $id = Admin::user()->id;

        $form = new Form(new $userModel);
        $form->edit($id);
        $form->action(route('admin.save.personal', ['id' => $id]));

        $form->item('username', '用户名')
            ->component(Input::make()->disabled());
        $form->item('phone', '电话号码')->component(Input::make()->showWordLimit()->maxlength(20));
        $form->item('name', '名称')->component(Input::make()->showWordLimit()->maxlength(20));
        $form->item('avatar', '头像')->component(Upload::make()->avatar()->path('avatar')->uniqueName());
        $form->item('password', '密码')->serveCreationRules(['required', 'string', 'confirmed'])->serveUpdateRules(['confirmed'])->ignoreEmpty()
            ->serveRulesMessage([
                'confirmed' => '两次输入的密码不一致'
            ])
            ->component(function () {
                return Input::make()->password()->showPassword();
            });
        $form->item('password_confirmation', '确认密码')
            ->copyValue('password')->ignoreEmpty()
            ->component(function () {
                return Input::make()->password()->showPassword();
            });

        $form->successRefData('pageReload', '');

        return $form;
    }

    public function savePersonal()
    {
        $data = request()->all();
        if ($validationMessages = $this->personal()->validatorData($data)) {
            return Admin::responseError($validationMessages);
        }

        $userModel = config('admin.database.users_model');
        $user = $userModel::find(Admin::user()->id);
        $user->name = $data['name'];
        $user->avatar = $data['avatar'];
        $user->phone = $data['phone'];
        if (isset($data['password']) && !empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        if ($user->save()) {
            return $this->responseMessage('编辑成功');
        }
        return $this->responseError('编辑失败');
    }
}

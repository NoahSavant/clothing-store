<?php

namespace App\Services;

use App\Constants\CommentConstants\CommentParent;
use App\Constants\FileConstants\FileCategory;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Models\Address;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Rate;
use App\Models\UsedTag;
use App\Models\User;
use Psy\Readline\Hoa\Console;

class CommentService extends BaseService
{
    public function __construct(Comment $comment)
    {
        $this->model = $comment;
    }

    public function get($input)
    {
        $search = $input['search'] ?? '';
        $id = $input['id'] ?? null;
        $type = $input['type'] ?? null;
        $hide = $input['hide'] ?? false;

        $user = auth()->user();

        if($user) {
            $userComment = $this->model->searchWithUser($search, $id, $type, $user->id)->first();
            $query = $this->model->searchWithOutUser($search, $id, $type, $user->id);
            $data = $this->getAll($input, $query);
            $data['user_comment'] = $userComment;
            return $data;
        }

        $query = $this->model->search($search, $id, $type, $hide);
        $data = $this->getAll($input, $query);
        return $data;
    }

    public function getSingle($id, $request)
    {
        $related = $request['related'] ?? false;
        if($related) {
            return $this->model->singleComment($id, $related)->get();
        }
        $comment = $this->model->singleComment($id, $related)->first();

        return $comment;
    }

    public function create($data) {
        if($this->isExisted($data)) {
            return [
                'errorMessage' => 'Không thể bình luận thêm'
            ];
        }


        $comment = parent::create([
            'user_id' => auth()->user()->id,
            'content' => $data['content'],
            'commentmorph_id' => $data['commentmorph_id'],
            'commentmorph_type' => CommentParent::getCommentParent($data['commentmorph_type']),
            'hide' => false
        ]);

        if(isset($data['rate']) && $data['commentmorph_type'] !== CommentParent::COMMENT) {
            Rate::create([
                'user_id' => auth()->user()->id,
                'value' => $data['rate'],
                'ratemorph_id' => $data['commentmorph_id'],
                'ratemorph_type' => CommentParent::getCommentParent($data['commentmorph_type']),
            ]);
        }

        if(!$comment) {
            return [
                'errorMessage' => 'Comment fail',
            ];
        }

        return [
            'successMessage' => 'Comment successfully',
            'data' => $comment
        ];
    }

    public function update($id, $data) {
        if ($this->isExisted($data['name'], $id)) {
            return [
                'errorMessage' => 'This name is existed'
            ];
        }

        $comment = $this->getFirst($id);

        if(!$comment) {
            return [
                'errorMessage' => 'Không tìm thấy'
            ];
        }

        $updateData = [
            'content' => $data['content'],
        ];

        if(isset($data['hide'])) {
            $updateData['hide'] = $data['hide'];
        }

        if (isset($data['rate'])) {
            Rate::where('user_id', $comment->user_id)
            ->where('ratemorph_id', $comment->commentmorph_id)
            ->where('ratemorph_type', $comment->commentmorph_type)->update([
                'value' => $data['rate']
            ]);
        }

        $comment = parent::update([$id], $updateData);

        if (!$comment) {
            return [
                'errorMessage' => 'Update comment fail',
            ];
        }

        return true;
    }

    public function delete($ids)
    {
        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete comment fail'
            ];
        }

        return $result;
    }

    public function isExisted($data, $id=null) {
        if($id) {
            return $this->model->where('user_id', isset($data['user_id']) ? $data['user_id'] : auth()->user()->id)
            ->where('commentmorph_id', $data['commentmorph_id'])
            ->where('commentmorph_type', CommentParent::getCommentParent($data['commentmorph_type']))
            ->whereNot('id', $id)->exists();
        }
        return $this->model->where('user_id', isset($data['user_id']) ? $data['user_id'] : auth()->user()->id)
            ->where('commentmorph_id', $data['commentmorph_id'])
            ->where('commentmorph_type', CommentParent::getCommentParent($data['commentmorph_type']))
            ->exists();
    }
}

<?php

namespace App\Services;

use App\Constants\CommentConstants\CommentParent;
use App\Constants\FileConstants\FileCategory;
use App\Constants\UserConstants\UserRole;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Models\Address;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Rate;
use App\Models\UsedTag;
use App\Models\User;
use DB;
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
        $rate = $input['rate'] ?? null;

        $user = auth()->user();

        if($user) {
            $userComment = $this->model->searchWithUser($search, $id, $type, $user->id)->first();
            $query = $this->model->searchWithOutUser($search, $id, $type, $user->id);
            $data = $this->getAll($input, $query);
            $data['user_comment'] = $userComment;
        } else {
            $query = $this->model->search($search, $id, $type, $hide, $rate);
            $data = $this->getAll($input, $query);
        }
        $avgRate = Rate::averageRate($id, $type)->first();
        $data['avg_rate'] = floatval($avgRate->average);
        $data['comment_right'] = $type == CommentParent::BLOG ? 1 : ($user->hasPurchasedProduct($id) ? 1 : 0);
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
                'value' => floatval($data['rate']),
                'ratemorph_id' => $data['commentmorph_id'],
                'ratemorph_type' => CommentParent::getCommentParent($data['commentmorph_type']),
            ]);
        }

        if(!$comment) {
            return [
                'errorMessage' => 'Bình luận thất bại',
            ];
        }

        return [
            'successMessage' => 'Bình luận thành công',
            'data' => $comment
        ];
    }

    public function update($id, $data) {
        $comment = $this->getFirst($id);

        if(!$comment) {
            return [
                'errorMessage' => 'Không tìm thấy'
            ];
        }

        $hasReplies = $this->model->where('commentmorph_id', $comment->id)
            ->where('commentmorph_type', Comment::class)
            ->exists();

        if ($hasReplies) {
            return [
                'errorMessage' => 'Bạn không thể thay đổi bình luận đã được phản hồi'
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
                'errorMessage' => 'Cập nhật bình luận thất bại',
            ];
        }

        return true;
    }

    public function delete($ids)
    {
        DB::beginTransaction();

        try {
            $comments = $this->model->whereIn('id', $ids)->get();

            foreach ($comments as $comment) {
                $hasReplies = $this->model->where('commentmorph_id', $comment->id)
                    ->where('commentmorph_type', Comment::class)
                    ->exists();

                if (!$hasReplies || auth()->user()->role !== UserRole::CUSTOMER) {
                    Rate::where('ratemorph_id', $comment->commentmorph_id)
                        ->where('ratemorph_type', $comment->commentmorph_type)
                        ->where('user_id', $comment->user_id)
                        ->delete();

                    $comment->delete();
                } else {
                    return [
                        'errorMessage' => 'Bạn không thể xóa bình luận đã được phản hồi'
                    ];
                }
            }

            DB::commit();

            return [
                'successMessage' => 'Xóa bình luận thành công'
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'errorMessage' => 'Xóa bình luận thất bại'
            ];
        }
    }

    public function isExisted($data, $id = null)
    {
        $query = $this->model
            ->where('user_id', isset($data['user_id']) ? $data['user_id'] : auth()->user()->id)
            ->where('commentmorph_id', $data['commentmorph_id'])
            ->where('commentmorph_type', CommentParent::getCommentParent($data['commentmorph_type']));

        if ($id) {
            $query->where('id', '!=', $id);
        }

        return $query->exists();
    }
}

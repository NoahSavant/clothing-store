<?php

namespace App\Services;

use App\Constants\FileConstants\FileCategory;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Models\Address;
use App\Models\Blog;
use App\Models\Product;
use App\Models\UsedTag;
use App\Models\User;
use Psy\Readline\Hoa\Console;

class BlogService extends BaseService
{
    public function __construct(Blog $blog)
    {
        $this->model = $blog;
    }

    public function get($input)
    {
        $search = $input['search'] ?? '';
        $tags = $input['tags'] ?? [];
        $status = $input['status'] ?? null;

        $query = $this->model->search($search, $tags, $status);
        $data = $this->getAll($input, $query);
        return $data;
    }

    public function getSingle($id, $request)
    {
        $related = $request['related'] ?? false;
        if($related) {
            return $this->model->singleBlog($id, $related)->get();
        }
        $blog = $this->model->singleBlog($id, $related)->first();

        return $blog;
    }

    public function create($data) {
        if($this->isExisted($data['name'])) {
            return [
                'errorMessage' => 'This name is existed'
            ];
        }

        $image_url = "https://res.cloudinary.com/dvcdmxgyk/image/upload/v1718962708/files/mcouvshn7gcajzyudvqv.jpg";

        if($data->hasFile('image')) {
            $result = $this->uploadFile($data->file('image'), 'blog_' . $data->get('name'), FileCategory::BLOG);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $image_url =  $result['data']['url'];
        }


        $blog = parent::create([
            'name' => $data['name'],
            'short_description' => $data['short_description'],
            'status' => $data['status'],
            'image_url' => $image_url,
            'content' => $data['content'],
            'user_id' => auth()->user()->id
        ]);

        if(!$blog) {
            return [
                'errorMessage' => 'Create blog fail',
            ];
        }

        if ($data->has('tags')) {
            foreach ($data['tags'] as $tag) {
                UsedTag::create([
                    'tag_id' => $tag,
                    'tagmorph_id' => $blog->id,
                    'tagmorph_type' => Blog::class
                ]);
            }
        }

        return [
            'successMessage' => 'Create blog successfully',
            'data' => $blog
        ];
    }

    public function update($id, $data) {
        if ($this->isExisted($data['name'], $id)) {
            return [
                'errorMessage' => 'This name is existed'
            ];
        }

        $updateData = [
            'name' => $data['name'],
            'short_description' => $data['short_description'],
            'status' => $data['status'],
            'content' => $data['content'],
        ];

        if ($data->hasFile('image')) {
            $result = $this->uploadFile($data->file('image'), 'blog_' . $data->get('name'), FileCategory::BLOG);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $updateData['image_url'] = $result['data']['url'];
        }

        $blog = parent::update([$id], $updateData);

        if (!$blog) {
            return [
                'errorMessage' => 'Update blog fail',
            ];
        }

        if ($data->has('tags')) {
            $currentTags = $this->getFirst($id)->tags->pluck('id')->toArray();

            $newTags = $data['tags'] == 'null' ? [] : $data['tags'];

            $tagsToDelete = array_diff($currentTags, $newTags);

            $tagsToAdd = array_diff($newTags, $currentTags);

            if (!empty($tagsToDelete)) {
                UsedTag::where('tagmorph_id', $id)
                    ->where('tagmorph_type', Blog::class)
                    ->whereIn('tag_id', $tagsToDelete)
                    ->delete();
            }

            if (!empty($tagsToAdd)) {
                foreach ($tagsToAdd as $tagId) {
                    UsedTag::create([
                        'tag_id' => $tagId,
                        'tagmorph_id' => $id,
                        'tagmorph_type' => Blog::class,
                    ]);
                }
            }
        }

        return true;
    }

    public function delete($ids)
    {
        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete blog fail'
            ];
        }

        return $result;
    }

    public function isExisted($name, $id=null) {
        if($id) {
            return $this->model->where('name', $name)->whereNot('id', $id)->exists();
        }
        return $this->model->where('name', $name)->exists();
    }
}

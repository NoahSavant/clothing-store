<?php

namespace App\Services;

use App\Constants\FileConstants\FileCategory;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Models\Address;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Models\UsedTag;
use App\Models\User;
use Psy\Readline\Hoa\Console;

class ProductService extends BaseService
{
    protected $variantService;
    public function __construct(Product $product, VariantService $variantService)
    {
        $this->model = $product;
        $this->variantService = $variantService;
    }

    public function get($input)
    {
        $search = $input['search'] ?? '';
        $tags = $input['tags'] ?? [];
        $status = $input['status'] ?? null;
        $collections = $input['collections'] ?? [];
        $collections = array_map('intval', $collections);
        $minPrice = $input['min_price'] ?? null;
        $maxPrice = $input['max_price'] ?? null;
        $excludeCollectionId = $input['excludeCollectionId'] ?? null;

        $query = $this->model->search($search, $tags, $status, $collections, $minPrice, $maxPrice, $excludeCollectionId);
        $data = $this->getAll($input, $query);
        return $data;
    }

    public function getColor($productId, $input)
    {
        $search = $input['search'] ?? '';

        $data = ProductColor::search($search, $productId)->get();
        return $data;
    }

    public function getSize($productId, $input)
    {
        $search = $input['search'] ?? '';

        $data = ProductSize::search($search, $productId)->get();
        return $data;
    }

    public function createColor($productId, $data)
    {
        $image_url = "https://res.cloudinary.com/dvcdmxgyk/image/upload/v1718962708/files/mcouvshn7gcajzyudvqv.jpg";

        if (isset($data['image'])) {
            $result = $this->uploadFile($data['image'], 'variant_' . $data['color'], FileCategory::VARIANT);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $image_url = $result['data']['url'];
        }

        $result = ProductColor::create([
            'product_id' => $productId,
            'color' => $data['color'],
            'image_url' => $image_url
        ]);

        return [
            'errorMessage' => $result ? null : 'Create color fail',
            'data' => $result
        ];
    }

    public function updateColor($id, $data)
    {
        $updateData = [
            'color' => $data['color'],
        ];

        if (isset($data['image'])) {
            $result = $this->uploadFile($data['image'], 'variant_' . $data['color'], FileCategory::VARIANT);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $updateData['image_url'] = $result['data']['url'];
        }

        $result = ProductColor::where('id', $id)->update($updateData);

        if ($result) {
            return $result;
        }

        return [
            'errorMessage' => 'Update color fail',
        ];
    }

    public function deleteColor($ids)
    {
        $result = ProductColor::destroy($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete color fail'
            ];
        }

        return $result;
    }

    public function deleteSize($ids)
    {
        $result = ProductSize::destroy($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete size fail'
            ];
        }

        return $result;
    }

    public function createSize($productId, $data)
    {
        $result = ProductSize::create([
            'product_id' => $productId,
            'size' => $data['size'],
        ]);

        return [
            'errorMessage' => $result ? null : 'Create size fail',
            'data' => $result
        ];
    }

    public function updateSize($id, $data)
    {
        $updateData = [
            'size' => $data['size'],
        ];

        $result = ProductSize::where('id', $id)->update($updateData);

        if ($result) {
            return $result;
        }

        return [
            'errorMessage' => 'Update size fail',
        ];
    }

    public function getSingle($id, $request)
    {
        $related = $request['related'] ?? false;
        if ($related) {
            return $this->model->singleBlog($id, $related)->get();
        }
        $product = $this->model->singleProduct($id)->first();

        return $product;
    }

    public function create($data) {
        if($this->isExisted($data['name'])) {
            return [
                'errorMessage' => 'This name is existed'
            ];
        }

        $first_image_url = "https://res.cloudinary.com/dvcdmxgyk/image/upload/v1718962708/files/mcouvshn7gcajzyudvqv.jpg";
        $second_image_url = "https://res.cloudinary.com/dvcdmxgyk/image/upload/v1718962708/files/mcouvshn7gcajzyudvqv.jpg";

        if($data->hasFile('first_image')) {
            $result = $this->uploadFile($data->file('first_image'), 'product_' . $data->get('name'), FileCategory::PRODUCT);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $first_image_url =  $result['data']['url'];
        }

        if ($data->hasFile('second_image')) {
            $result = $this->uploadFile($data->file('second_image'), 'product_' . $data->get('name'), FileCategory::PRODUCT);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $second_image_url = $result['data']['url'];
        }


        $product = parent::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'short_description' => $data['short_description'],
            'category_id' => $data['category_id'],
            'status' => $data['status'],
            'first_image_url' => $first_image_url,
            'second_image_url' => $second_image_url,
            'note' => $data['note'],
        ]);

        if(!$product) {
            return [
                'errorMessage' => 'Create product fail',
            ];
        }

        if ($data->has('tags')) {
            foreach ($data['tags'] as $tag) {
                UsedTag::create([
                    'tag_id' => $tag,
                    'tagmorph_id' => $product->id,
                    'tagmorph_type' => Product::class
                ]);
            }
        }

        $error = 0;

        if ($data->has('variants')) {
            foreach ($data['variants'] as $variant) {
                $result = $this->variantService->createVariant($product->id, $variant);
                if(!$result['data']) {
                    $error += 1;
                }
            }
        }

        if($error == 0) {
            return [
                'successMessage' => 'Create product successfully',
                'data' => $product
            ];
        }

        return [
            'errorMessage' => 'Create product successfully but ' . $error . 'has fail to create',
            'data' => $product
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
            'description' => $data['description'],
            'short_description' => $data['short_description'],
            'category_id' => $data['category_id'],
            'status' => $data['status'],
            'note' => $data['note'],
        ];

        if ($data->hasFile('first_image')) {
            $result = $this->uploadFile($data->file('first_image'), 'product_' . $data->get('name'), FileCategory::PRODUCT);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $updateData['first_image_url'] = $result['data']['url'];
        }

        if ($data->hasFile('second_image')) {
            $result = $this->uploadFile($data->file('second_image'), 'product_' . $data->get('name'), FileCategory::PRODUCT);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $updateData['second_image_url'] = $result['data']['url'];
        }


        $product = parent::update([$id], $updateData);

        if (!$product) {
            return [
                'errorMessage' => 'Update product fail',
            ];
        }

        if ($data->has('tags')) {
            $currentTags = $this->getFirst($id)->tags->pluck('id')->toArray();

            $newTags = $data['tags'] == 'null' ? [] : $data['tags'];

            $tagsToDelete = array_diff($currentTags, $newTags);

            $tagsToAdd = array_diff($newTags, $currentTags);

            if (!empty($tagsToDelete)) {
                UsedTag::where('tagmorph_id', $id)
                    ->where('tagmorph_type', Product::class)
                    ->whereIn('tag_id', $tagsToDelete)
                    ->delete();
            }

            if (!empty($tagsToAdd)) {
                foreach ($tagsToAdd as $tagId) {
                    UsedTag::create([
                        'tag_id' => $tagId,
                        'tagmorph_id' => $id,
                        'tagmorph_type' => Product::class,
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
                'errorMessage' => 'Delete product fail'
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

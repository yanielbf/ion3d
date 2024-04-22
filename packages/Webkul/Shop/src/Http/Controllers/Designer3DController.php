<?php

namespace Webkul\Shop\Http\Controllers;

use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class Designer3DController extends Controller
{
    /**
     * Using const variable for status
     */
    const STATUS = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected ThemeCustomizationRepository $themeCustomizationRepository)
    {
    }

    /**
     * Loads the home page for the storefront.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $attributeFamily = request()->query('attribute_family') ?? '3d_phone_cover';
        $attributes = request()->query('attributes') ?? [];
        
        $values = [];

        foreach($attributes as $attribute) {
            $aux = explode('-', $attribute);
            if(count($aux) == 2) {
                $values[$aux[0]] = intval($aux[1]); 
            }
        }
        
        $customization = $this->themeCustomizationRepository->orderBy('sort_order')->findOneWhere([
            'status'     => self::STATUS,
            'channel_id' => core()->getCurrentChannel()->id,
            'type' => 'designer_3d'
        ]);

        return view('shop::designer3d.index', ['attribute_family_3d' => $attributeFamily, 'attributes_3d' => $values, 'customization' => $customization]);
    }

    /**
     * Loads the home page for the storefront if something wrong.
     *
     * @return \Exception
     */
    public function notFound()
    {
        abort(404);
    }
}

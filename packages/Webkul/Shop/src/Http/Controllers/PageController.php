<?php

namespace Webkul\Shop\Http\Controllers;

use Webkul\CMS\Repositories\PageRepository;
use Webkul\Marketing\Repositories\URLRewriteRepository;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class PageController extends Controller
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
    public function __construct(
        protected PageRepository $pageRepository,
        protected URLRewriteRepository $urlRewriteRepository,
        protected ThemeCustomizationRepository $themeCustomizationRepository
    ) {
    }

    /**
     * To extract the page content and load it in the respective view file
     *
     * @param  string  $urlKey
     * @return \Illuminate\View\View
     */
    public function view($urlKey)
    {
        $page = $this->pageRepository->findByUrlKey($urlKey);

        if (! $page) {
            $urlRewrite = $this->urlRewriteRepository->findOneWhere([
                'entity_type'  => 'cms_page',
                'request_path' => $urlKey,
                'locale'       => app()->getLocale(),
            ]);

            if ($urlRewrite) {
                return redirect()->to($urlRewrite->target_path, $urlRewrite->redirect_type);
            }
        }

        $customizations = $this->themeCustomizationRepository->orderBy('sort_order')->findWhere([
            'status'     => self::STATUS,
            'channel_id' => core()->getCurrentChannel()->id,
        ]);

        return view('shop::cms.page')->with('page', $page)->with('customizations', $customizations);
    }
}

<?php
/**
 * Wizard
 *
 * Original Code Copyright
 * @license     Apache2.0
 * @link        https://aicode.cc/
 * @copyright   管宜尧 <mylxsw@aicode.cc>
 *
 * Modified Code Copyright
 * @license     MPL2.0
 * @link        https://github.com/XingfenD
 * @copyright   Fendy <xingfen.fendy@outlook.com>
 *
 * Modifications:
 *  1. Compare document by page external id instead of page id
 */

namespace App\Http\Controllers;


use App\Repositories\Document;
use Illuminate\Http\Request;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;


class CompareController extends Controller
{

    /**
     * 文档比较
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function compare(Request $request)
    {
        $this->validate(
            $request,
            [
                'doc1title' => 'required',
                'doc2title' => 'required',
                'noheader'  => 'in:0,1',
                'act'       => 'in:compare,diff'
            ]
        );

        $doc1title = $request->input('doc1title');
        $doc1      = $request->input('doc1', '');
        $doc1p_page_external_id = $request->input('doc1p_page_external_id');

        $doc2title = $request->input('doc2title');
        $doc2      = $request->input('doc2', '');
        $doc2p_page_external_id = $request->input('doc2p_page_external_id');

        $viewData = [
            'doc1'      => $doc1,
            'doc2'      => $doc2,
            'doc1title' => $doc1title,
            'doc2title' => $doc2title,
            'doc1p_page_external_id'   => $doc1p_page_external_id,
            'doc2p_page_external_id'   => $doc2p_page_external_id,
            'noheader'  => !!$request->input('noheader', 0)
        ];

        $act = $request->input('act', 'diff');
        if ($act === 'diff') {
            $differ = new Differ(new UnifiedDiffOutputBuilder("--- Original\n+++ New\n", true));

            if (isJson($doc1) && isJson($doc2)) {
                $doc1 = json_encode(json_decode($doc1), JSON_PRETTY_PRINT);
                $doc2 = json_encode(json_decode($doc2), JSON_PRETTY_PRINT);
            }

            $viewData['differContents'] = $differ->diff($doc2, $doc1);
        }

        return view("doc.{$act}", $viewData);
    }
}
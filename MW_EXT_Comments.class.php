<?php

namespace MediaWiki\Extension\MW_EXT_Comments;

use OutputPage, Parser, Skin;
use MediaWiki\Extension\MW_EXT_Core\MW_EXT_Core;

/**
 * Class MW_EXT_Comments
 * ------------------------------------------------------------------------------------------------------------------ */
class MW_EXT_Comments {

	/**
	 * Register tag function.
	 *
	 * @param Parser $parser
	 *
	 * @return bool
	 * @throws \MWException
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'comments', __CLASS__ . '::onRenderTag' );

		return true;
	}

	/**
	 * Render tag function.
	 *
	 * @param Parser $parser
	 * @param string $type
	 * @param string $id
	 *
	 * @return bool|string
	 * @throws \ConfigException
	 * @throws \MWException
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onRenderTag( Parser $parser, $type = '', $id = '' ) {
		// Argument: type.
		$getType = MW_EXT_Core::outClear( $type ?? '' ?: '' );

		// Argument: ID.
		$getID = MW_EXT_Core::outClear( $id ?? '' ?: '' );

		// Check page status.
		if ( ! MW_EXT_Core::getTitle() || ! MW_EXT_Core::getTitle()->isContentPage() || ! MW_EXT_Core::getWikiPage() ) {
			return null;
		}

		switch ( $getType ) {
			case 'disqus':
				// Build data.
				$siteURL = MW_EXT_Core::getConfig( 'Server' );
				$pageURL = $siteURL . '/?curid=' . MW_EXT_Core::getTitle()->getArticleID();
				$pageID  = MW_EXT_Core::getTitle()->getArticleID();

				// Out type.
				$outType = '<div id="disqus_thread"></div>';
				$outType .= '<script>let disqus_config = function () { this.page.url = "' . $pageURL . '"; this.page.identifier = "' . $pageID . '"; };</script>';
				$outType .= '<script>(function() { let d = document, s = d.createElement("script"); s.src = "https://' . $getID . '.disqus.com/embed.js"; s.setAttribute("data-timestamp", +new Date()); (d.head || d.body).appendChild(s); })();</script>';
				break;
			case 'facebook':
				$outType = '<div id="mw-ext-comments-fb" class="fb-comments" data-href="https://developers.facebook.com/docs/plugins/comments#configurator" data-numposts="5"></div>';
				break;
			case 'vk':
				// Build data.
				$siteURL = MW_EXT_Core::getConfig( 'Server' );
				$pageURL = $siteURL . '/?curid=' . MW_EXT_Core::getTitle()->getArticleID();
				$pageID  = MW_EXT_Core::getTitle()->getArticleID();

				// Out type.
				$outType = '<script>VK.init({apiId: ' . $getID . ', onlyWidgets: true});</script>';
				$outType .= '<div id="mw-ext-comments-vk"></div>';
				$outType .= '<script>VK.Widgets.Comments("mw-ext-comments-vk", {limit: 15, attach: "*", pageUrl: "' . $pageURL . '"});</script>';
				break;
			default:
				$parser->addTrackingCategory( 'mw-ext-comments-error-category' );

				return null;
		}

		// Out HTML.
		$outHTML = '<div class="mw-ext-comments navigation-not-searchable">' . $outType . '</div>';

		// Out parser.
		$outParser = $parser->insertStripItem( $outHTML, $parser->mStripState );

		return $outParser;
	}

	/**
	 * Load resource function.
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 *
	 * @return bool|null
	 * @throws \MWException
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		if ( ! MW_EXT_Core::getTitle() || ! MW_EXT_Core::getTitle()->isContentPage() || ! MW_EXT_Core::getWikiPage() ) {
			return null;
		}

		$out->addHeadItem( 'mw-ext-comments-vk', '<script src="https://vk.com/js/api/openapi.js"></script>' );
		$out->addModuleStyles( [ 'ext.mw.comments.styles' ] );

		return true;
	}
}

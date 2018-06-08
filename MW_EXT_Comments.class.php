<?php

/**
 * Class MW_EXT_Comments
 * ------------------------------------------------------------------------------------------------------------------ */

class MW_EXT_Comments {

	/**
	 * Clear DATA (escape html).
	 *
	 * @param $string
	 *
	 * @return string
	 * -------------------------------------------------------------------------------------------------------------- */

	private static function clearData( $string ) {
		$outString = htmlspecialchars( trim( $string ), ENT_QUOTES );

		return $outString;
	}

	/**
	 * Get configuration parameters.
	 *
	 * @param $getData
	 *
	 * @return mixed
	 * @throws ConfigException
	 * -------------------------------------------------------------------------------------------------------------- */

	private static function getConfig( $getData ) {
		$context   = new RequestContext();
		$getConfig = $context->getConfig()->get( $getData );

		return $getConfig;
	}

	/**
	 * Get `getTitle`.
	 *
	 * @return null|Title
	 * -------------------------------------------------------------------------------------------------------------- */
	private static function getTitle() {
		$context  = new RequestContext();
		$getTitle = $context->getTitle();

		return $getTitle;
	}

	/**
	 * Register tag function.
	 *
	 * @param Parser $parser
	 *
	 * @return bool
	 * @throws MWException
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
	 * @throws ConfigException
	 * @throws MWException
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onRenderTag( Parser $parser, $type = '', $id = '' ) {
		$context = new RequestContext();

		// Argument: type.
		$getType = self::clearData( $type ?? '' ?: '' );

		// Argument: ID.
		$getID = self::clearData( $id ?? '' ?: '' );

		// Check page status.
		if ( ! $context->getTitle() || ! $context->getTitle()->isContentPage() || ! $context->getWikiPage() ) {
			return false;
		}

		switch ( $getType ) {
			case 'disqus':
				// Build data.
				$siteURL = self::getConfig( 'Server' );
				$pageURL = $siteURL . '/?curid=' . self::getTitle()->getArticleID();
				$pageID  = self::getTitle()->getArticleID();

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
				$siteURL = self::getConfig( 'Server' );
				$pageURL = $siteURL . '/?curid=' . self::getTitle()->getArticleID();
				$pageID  = self::getTitle()->getArticleID();

				// Out type.
				$outType = '<script>VK.init({apiId: ' . $getID . ', onlyWidgets: true});</script>';
				$outType .= '<div id="mw-ext-comments-vk"></div>';
				$outType .= '<script>VK.Widgets.Comments("mw-ext-comments-vk", {limit: 15, attach: "*", pageUrl: "' . $pageURL . '"});</script>';
				break;
			default:
				$parser->addTrackingCategory( 'mw-ext-comments-error-category' );

				return false;
		}

		// Out HTML.
		$outHTML = '<div class="mw-ext-comments">' . $outType . '</div>';

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
	 * @return bool
	 * @throws MWException
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$context = new RequestContext();

		if ( ! $context->getTitle() || ! $context->getTitle()->isContentPage() || ! $context->getWikiPage() ) {
			return false;
		}

		$out->addHeadItem( 'mw-ext-comments-vk', '<script src="https://vk.com/js/api/openapi.js"></script>' );
		$out->addModuleStyles( array( 'ext.mw.comments.styles' ) );

		return true;
	}
}

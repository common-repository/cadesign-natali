<?

namespace Cadesign\NataliApi;

use Cadesign\Natali\Assoc;
use Cadesign\Natali\Helper;
use Cadesign\Natali\WooCommerceApi;

class DeleteProduct
{
	public static function deleteById($wpID, $nataliID)
	{
		$woocommerce = new WooCommerceApi();
		$variations = $woocommerce->getClient()->get('products/' . $wpID . '/variations');
		$product = $woocommerce->getClient()->get('products/' . $wpID);

		foreach ($variations as $variation)
		{
			$res = wp_delete_attachment($variation->image->id, true);

			$woocommerce->getClient()->delete('products/' . $wpID . '/variations/' . $variation->id, ['force' => true]);
		}

		foreach ($product->images as $image)
		{
			$res = wp_delete_attachment($image->id, true);
		}
		$woocommerce->getClient()->delete('products/' . $wpID, ['force' => true]);

		Helper::Log('Товар ' . $wpID . ' удален');
		Assoc::deleteAssoc($nataliID);
	}
}
#!/bin/bash
# Setup script for TopHostMonzphere module on Zabbix 7.4
# Run this script from the module directory: /usr/share/zabbix/ui/modules/TopHostMonzphere/

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
NATIVE_TOPHOSTS="/usr/share/zabbix/ui/widgets/tophosts"

echo "Setting up TopHostMonzphere for Zabbix 7.4..."

# Check if native tophosts widget exists
if [ ! -d "$NATIVE_TOPHOSTS" ]; then
    echo "ERROR: Native tophosts widget not found at $NATIVE_TOPHOSTS"
    exit 1
fi

# Copy required files from native tophosts widget
echo "Copying CWidgetFieldColumnsList.php..."
cp "$NATIVE_TOPHOSTS/includes/CWidgetFieldColumnsList.php" "$SCRIPT_DIR/includes/"

echo "Copying CWidgetFieldColumnsListView.php..."
cp "$NATIVE_TOPHOSTS/includes/CWidgetFieldColumnsListView.php" "$SCRIPT_DIR/includes/"

# Update namespace in copied files
echo "Updating namespaces..."
sed -i 's/namespace Widgets\\TopHosts\\Includes;/namespace Modules\\TopHostsMonzphere\\Includes;/g' "$SCRIPT_DIR/includes/CWidgetFieldColumnsList.php"
sed -i 's/namespace Widgets\\TopHosts\\Includes;/namespace Modules\\TopHostsMonzphere\\Includes;/g' "$SCRIPT_DIR/includes/CWidgetFieldColumnsListView.php"

# Update any internal references to the Widget class
sed -i 's/use Widgets\\TopHosts\\Widget;/use Modules\\TopHostsMonzphere\\Widget;/g' "$SCRIPT_DIR/includes/CWidgetFieldColumnsList.php"
sed -i 's/use Widgets\\TopHosts\\Widget;/use Modules\\TopHostsMonzphere\\Widget;/g' "$SCRIPT_DIR/includes/CWidgetFieldColumnsListView.php"

# Set correct ownership
echo "Setting ownership..."
chown -R www-data:www-data "$SCRIPT_DIR"

echo "Setup complete! Restart PHP-FPM with: systemctl restart php*-fpm"

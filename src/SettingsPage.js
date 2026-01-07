import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {
  __experimentalText as Text,
  __experimentalHeading as Heading,
  __experimentalVStack as VStack,
  __experimentalHStack as HStack,
  __experimentalToggleGroupControl as ToggleGroupControl,
  __experimentalToggleGroupControlOption as ToggleGroupControlOption,
  ColorPalette,
  TextControl,
  ToggleControl,
  RangeControl,
  Button,
  Spinner,
  Snackbar,
} from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";

export default function SettingsPage(props) {
  const [settings, setSettings] = useState(null);
  console.log(settings);
  const [isSaving, setIsSaving] = useState(false);
  const [showNotice, setShowNotice] = useState(false);

  // Charger uniquement les settings lorsque le composant est chargé
  useEffect(() => {
    apiFetch({
      path: "/proxilog-features/v1/settings",
      method: "GET",
    })
      .then(response => {
        setSettings(response);
      })
      .catch(error => {
        console.error("Error loading settings:", error);
      });
  }, []);

  // Gestion de l'enregistrement des settings
  const handleSaveSettings = () => {
    setIsSaving(true);

    apiFetch({
      path: "/proxilog-features/v1/settings",
      method: "POST",
      data: settings,
    }).then(response => {
      setShowNotice(true);
      setIsSaving(false);

      setTimeout(() => setShowNotice(false), 3000);
    });
  };

  const colors = [
    { name: "red", color: "#f00" },
    { name: "white", color: "#fff" },
    { name: "blue", color: "#00f" },
  ];

  return (
    <>
      <HStack as="header" spacing={4} className="proxilog-header">
        <VStack spacing={2}>
          <Heading level={1}>Proxilog Features</Heading>
          <Text variant="muted">
            This is the settings page for the Proxilog Features plugin.
          </Text>
        </VStack>
        <Button
          variant="primary"
          onClick={handleSaveSettings}
          isBusy={isSaving}
          disabled={isSaving}
        >
          {isSaving
            ? __("Saving…", "proxilog-features")
            : __("Save Settings", "proxilog-features")}
        </Button>
      </HStack>
      <VStack as="main" spacing={2} className="proxilog-content">
        {settings ? (
          <VStack spacing={8} className="proxilog-content-form">
            <ToggleControl
              checked={settings.isEnabled}
              label={__("Enable something", "proxilog-features")}
              help={__("This is a help text", "proxilog-features")}
              onChange={() => {
                setSettings({
                  ...settings,
                  isEnabled: !settings.isEnabled || false,
                });
              }}
              __nextHasNoMarginBottom
            />

            <TextControl
              __next40pxDefaultSize
              __nextHasNoMarginBottom
              onChange={value => {
                setSettings({
                  ...settings,
                  text: value,
                });
              }}
              label="Champ texte"
              type="text"
              value={settings.text}
              help="Le titre à afficher dans la section"
            />

            <RangeControl
              __next40pxDefaultSize
              __nextHasNoMarginBottom
              help="Please select how transparent you would like this."
              initialPosition={settings.range}
              label="Opacity"
              min={0}
              max={100}
              onChange={value => {
                setSettings({
                  ...settings,
                  range: value,
                });
              }}
            />

            <ToggleGroupControl
              __next40pxDefaultSize
              __nextHasNoMarginBottom
              isBlock
              label="Label"
              value={settings.position}
              onChange={value => {
                setSettings({
                  ...settings,
                  position: value,
                });
              }}
            >
              <ToggleGroupControlOption label="Left" value="left" />
              <ToggleGroupControlOption label="Center" value="center" />
              <ToggleGroupControlOption label="Right" value="right" />
              <ToggleGroupControlOption label="Justify" value="justify" />
            </ToggleGroupControl>

            <ColorPalette
              colors={colors}
              value={settings.color}
              onChange={value => {
                setSettings({
                  ...settings,
                  color: value,
                });
              }}
            />
          </VStack>
        ) : (
          <Spinner />
        )}
      </VStack>
      {showNotice && (
        <Snackbar
          className="proxilog-snackbar"
          explicitDismiss={true}
          onRemove={() => setShowNotice(false)}
          type="success"
        >
          {__("Settings saved.", "proxilog-features")}
        </Snackbar>
      )}
    </>
  );
}

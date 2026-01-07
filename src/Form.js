import {
  __experimentalVStack as VStack,
  __experimentalToggleGroupControl as ToggleGroupControl,
  __experimentalToggleGroupControlOption as ToggleGroupControlOption,
  ColorPalette,
  TextControl,
  ToggleControl,
  RangeControl,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";

export default function Form(props) {
  const { settings, onChange } = props;

  const colors = [
    { name: "red", color: "#f00" },
    { name: "white", color: "#fff" },
    { name: "blue", color: "#00f" },
  ];

  const handleChange = (key, value) => {
    onChange({
      ...settings,
      [key]: value,
    });
  };

  return (
    <VStack spacing={8} className="proxilog-content-form">
      <ToggleControl
        checked={settings.isEnabled}
        label={__("Enable something", "proxilog-features")}
        help={__("This is a help text", "proxilog-features")}
        onChange={() => {
          handleChange("isEnabled", !settings.isEnabled);
        }}
        __nextHasNoMarginBottom
      />

      <TextControl
        __next40pxDefaultSize
        __nextHasNoMarginBottom
        onChange={value => {
          handleChange("text", value);
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
          handleChange("range", value);
        }}
      />

      <ToggleGroupControl
        __next40pxDefaultSize
        __nextHasNoMarginBottom
        isBlock
        label="Label"
        value={settings.position}
        onChange={value => {
          handleChange("position", value);
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
          handleChange("color", value);
        }}
      />
    </VStack>
  );
}
